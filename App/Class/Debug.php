<?php

function internal_error_handler_hide ($errno, $errstr, $errfile, $errline)
{

}

function internal_error_handler_ignore ($errno, $errstr, $errfile, $errline)
{
//	echo '['.$errno.':'.$errfile.'@'.$errline.'] '.$errstr."\n1<br />";
}

function internal_exception_handler_ignore ($exception)
{
	echo "Uncaught exception: " , $exception->getMessage (), "\n";
}

/**
 *
 * @desc Класс для отладки.
 * @author Yury Shvedov
 * @package IcEngine
 *
 */
class Debug
{

	const ERROR_HANDLER_HIDE = 'hide';

	const ERROR_HANDLER_IGNORE = 'ignore';

	const EXCEPTION_HANDLER_IGNORE = 'ignore';

	/**
	 * @desc Пресеты конфигов (можно задавать одним названием).
	 * @var array
	 */
	protected static $_configPresets = array (
		// Вывод сообщений отладки на экран
		'echo'	=> array (
			'echo_active'		=> true,
		),
		// Только firebug
		'fb'	=> array (
			'firebug_active'	=> true
		),
		// Вывод ошибок на экран
		true	=> array (
			'echo_active'		=> true
		)
	);

	public static $config = array (

		/**
		 * @desc Ведение лога в БД.
		 * @var boolean
		 */
		'database_active'			=> false,

		/**
		 * @desc Название таблицы логов в БД.
		 * @var string
		 */
		'database_table'			=> 'log',

		/**
		 * @desc Остановить выполнение скрипта при ошибке.
		 * @var boolean
		 */
		'die_on_error'				=> true,

		/**
		 * @desc Отображение в браузер, вывод через stdOut.
		 * @var boolean
		 */
		'echo_active'				=> false,

		/**
		 * @desc Отображение в файл.
		 * @var boolean
		 */
		'file_active'				=> false,

		/**
		 * @desc Файл для записи сообщений об ошибках.
		 * @var string
		 */
		'file_error'				=> 'error.txt',

		/**
		 * @desc Файл для записи сообщений о варнингах.
		 * @var string
		 */
		'file_warn'					=> 'warning.txt',

		/**
		 * @desc Файл для записи прочих сообщений.
		 * @var string
		 */
		'file_log'					=> 'notice.txt',

		/**
		 * @desc Отображение в FireBug
		 * @var boolean
		 */
		'firebug_active'			=> false,

		/**
		 * @desc Следим за количеством дебаг сообщений, чтобы
		 * длина заголовка не превысила максимально допустимую,
		 * иначе на странице вместо текста будет "X-Wf-1-1-1-32: 201|...."
		 *
		 * @var integer
		 */
		'firebug_messages_limit'	=> 11,

		/**
		 * @desc Игнорировать варнинг open basedir.
		 * @var boolean
		 */
		'ignore_open_basedir_warning'	=> true,

		/**
		 * @desc Игнорировать варнинг при unlink.
		 * @var boolean
		 */
		'ignore_unlink_warning'			=> true,

		/**
		 * @desc Вывод на экран трасировки.
		 * @var boolean
		 */
		'print_backtrace'				=> false,

		/**
		 * @desc Стандартный лог PHP.
		 * @var boolean
		 */
		'phplog'						=> true
	);

	/**
	 * @desc Количество выведенных сообщений.
	 * Важно ограничить вывод через FirePHP, чтобы длина заголовка не
	 * превысила 1024
	 * @var integer
	 */
	public static $debug_messages_count = 0;

	/**
	 * @desc Время подключения класса дебага.
	 * @var integer
	 */
	public static $startTime;

	/**
	 * @desc Время последнего замера времени.
	 * @var integer
	 */
	public static $lastTime;

	/**
	 * @desc Скрытие всех возникающих ошибок.
	 */
	public static function disable ($default_display = false)
	{
		error_reporting (null);
		ini_set ('display_errors', $default_display);
		ini_set ('html_errors', $default_display);
		ini_set ('track_errors', true);

		set_error_handler ('internal_error_handler_hide');
	}

	/**
	 * @desc Внутренний обработчик ошибок.
	 * @param string $errno Код ошибки.
	 * @param string $errstr Текст ошибки.
	 * @param string $errfile Файл.
	 * @param string $errline Строка.
	 * @return boolean
	 */
	public static function errorHandler ($errno, $errstr, $errfile, $errline)
	{
		if (
			// Игнорим сообщение про open_basedir из smarty
			(
				self::$config ['ignore_open_basedir_warning'] &&
				$errno == E_WARNING &&
				strpos ($errfile, 'smarty/internals/core.get_include_path.php')
			) ||
			// Варнинг unlink
			(
				self::$config ['ignore_unlink_warning'] &&
				$errno == E_WARNING &&
				substr ($errstr, 0, 7) == 'unlink('
			)
		)
		{
			return false;
		}

		if (self::$config ['print_backtrace'])
		{
			echo '<pre>';
			debug_print_backtrace ();
			echo '</pre>';
		}

		$debug = array_slice (debug_backtrace (), 1, 10);
		self::removeUninterestingObjects ($debug);

		$log_text =
			(
				isset ($_SERVER ['HTTP_HOST']) ?
				$_SERVER ['HTTP_HOST'] :
				'empty host'
			) .
			(
				isset ($_SERVER ['REQUEST_URI']) ?
				$_SERVER ['REQUEST_URI'] :
				'/empty uri'
			) .
			(
				isset ($_SERVER ['HTTP_REFERER']) ?
				"\r\nreferer: " . $_SERVER ['HTTP_REFERER']  :
				''
			) .
			"\r\n" .
			'[' . $errno . ':' . $errfile . '@' . $errline . '] ' .
			$errstr . "\r\n";

		foreach ($debug as $debug_step)
		{
			if (isset ($debug_step ['file']))
			{
				$log_text .=
					'[' . $debug_step ['file'] . '@' .
					$debug_step ['line'] . ':' .
					$debug_step ['function'] . ']' . "\r\n";
			}
			else
			{
				break;
			}
		}

		self::log ($log_text, $errno);

		if (
			($errno == E_ERROR || $errno == E_USER_ERROR) &&
			self::$config ['die_on_error']
		)
		{
			die ("<b>Terminated on fatal error.</b><br />" . $log_text);
		}

		return true;
	}

	/**
	 * @desc Внутренний обработчик ошибок.
	 * @param Exception $e Объект ошики.
	 */
	public static function exceptionHandler (Exception $e)
	{
		if (self::$config ['print_backtrace'])
		{
			echo '<pre>' . $e->getTraceAsString () . '</pre>';
		}

		$debug = array_slice ($e->getTrace (), 1, 10);
		self::removeUninterestingObjects ($debug);

		$log_text =
			(
				isset ($_SERVER ['HTTP_HOST']) ?
				$_SERVER ['HTTP_HOST'] :
				'empty host'
			) .
			(
				isset ($_SERVER ['REQUEST_URI']) ?
				$_SERVER ['REQUEST_URI'] :
				'/empty uri'
			) .
			(
				isset ($_SERVER ['HTTP_REFERER']) ?
				"\r\nreferer: " . $_SERVER ['HTTP_REFERER']  :
				''
			) .
			"\r\n" .
			'[' . E_ERROR . ':' . $e->getFile() . '@' . $e->getLine() . '] ' .
			$e->getMessage() . "\r\n";

		foreach ($debug as $debug_step)
		{
			if (isset ($debug_step ['file']))
			{
				$log_text .=
					'[' . $debug_step ['file'] . '@' .
					$debug_step ['line'] . ':' .
					$debug_step ['function'] . ']' . "\r\n";
			}
			else
			{
				break;
			}
		}

		self::log ($log_text, E_ERROR);
	}

	/**
	 * @desc Включение внутреннего обработчика ошибок.
	 * @param mixed $config Настройки.
	 */
	public static function init ($config)
	{
		if ($config === false)
		{
			self::disable ();
		}

		error_reporting (E_ALL | E_STRICT);

		ini_set ('display_errors', false);
		ini_set ('html_errors', true);
		ini_set ('track_errors', true);

		$memory_start = function_exists ('memory_get_usage') ? memory_get_usage(true) : 0;

		foreach (func_get_args () as $cfg)
		{
			self::setOptions ($cfg);
		}

		set_error_handler (array (__CLASS__, 'errorHandler'));
		set_exception_handler (array(__CLASS__, 'exceptionHandler'));
		register_shutdown_function (array (__CLASS__, 'shutdownHandler'));
	}

	/**
	 * @desc Форматированный вывод по средствам print_r.
	 * @param mixed $var
	 */
	public static function printr ($var)
	{
		echo '<pre>';

		foreach (func_get_args () as $arg)
		{
			echo str_replace (
				array (
					'<',
					'>'
				),
				array (
					'&lt;',
					'&gt;'
				),
				print_r ($arg, true)
			) . "\n";
		}

		echo '</pre>';
	}

	/**
	 * @desc Форматированный вывод переменных по средствам var_export.
	 * @param mixed $var Переменная
	 */
	public static function vardump ($var)
	{
		echo '<pre>';
		
		foreach (func_get_args () as $var)
		{
			echo str_replace (
				array (
					"=>\n",
					"=> \n",
					'<',
					'>'
				),
				array (
					'=&gt;',
					'=&gt;',
					'&lt;',
					'&gt;'
				),
				var_export ($var, true)
			) . "\n";
		}

		echo '</pre>';
	}

	/**
	 * @desc Удаление объекта БД из лога, иначе
	 * логин/пароль от базы могут быть отправлены пользователю
	 */
	public static function removeUninterestingObjects (array &$debug_trace)
	{
		// du - debug_unit
		foreach ($debug_trace as &$du)
		{
			if (
				(isset ($du ['class']) && $du ['class'] == 'DDS') ||
				(isset ($du ['class']) && strncmp ($du ['class'], 'Db_', 3) == 0)
			)
			{
				// БД
				$du = array(
					'file'		=> $du ['file'],
					'line'		=> $du ['line'],
					'function'	=> $du ['function']
				);
			}
		}
	}

	/**
	 * @desc Установка настроек для дебага.
	 * @param array|Config_Abstract $config Конфиг.
	 */
	public static function setOptions ($config)
	{
		if (is_scalar ($config))
		{
			if (isset (self::$_configPresets [$config]))
			{
				// подключение файрпхп
				if ($config == 'fb' && !function_exists ('fb'))
				{
					require dirname (__FILE__) . '/../includes/FirePHPCore/fb.php';
				}
				$config = self::$_configPresets [$config];
			}
			elseif (strpos ($config, 'dir:') === 0)
			{
				$path = rtrim (substr ($config, 4), '\\/') . '/';
				$config = array (
					'file_active'		=> true,
					'file_error'		=> $path . 'error.txt',
					'file_warn'			=> $path . 'warning.txt',
					'file_log'			=> $path . 'notice.txt'
				);
			}
		}

		if (is_object ($config) && $config instanceof Objective)
		{
			$config = $config->__toArray ();
		}

		if (!$config)
		{
			return;
		}

		self::$config = array_merge (self::$config, $config);
	}

	/**
	 * @desc Устанавливает режим отображения ошибок.
	 * @param boolean|string|null $database Таблица БД. Если передано null,
	 * этот метод будет активен если есть возможность вывода в БД.
	 * @param boolean $echo Вывод в браузер.
	 * @param boolean|null $firebug FireBug.
	 * @param string|null $file Вывод в файл - имя файла.
	 */
	public static function setOutput ($database = null, $echo = true,
		$firebug = null, $file = null)
	{
		// БД
		if ($database === null)
		{
			self::$config ['database_active'] = class_exists ('DDS');
		}
		elseif ($database)
		{
			self::$config ['database_active'] = true;
			if (is_string ($database))
			{
				self::$config ['database_table'] = $database;
			}
		}
		else
		{
			self::$config ['database_active'] = false;
		}

		// Браузер
		self::$config ['echo_active'] = (bool) $echo;

		// FireBug
		self::$config ['firebug_active'] = (
			(is_null ($firebug) && function_exists ('fb')) ||
			$firebug
		);

		// Файл
		if (is_string ($file))
		{
			self::$config ['file_active']	= true;
			self::$config ['file_error']	= $file;
			self::$config ['file_warn']		= $file;
			self::$config ['file_log']		= $file;
		}
		else
		{
			self::$config ['file_active'] = false;
		}
	}

	/**
	 * @desc Обработчик завершения работы скрипта
	 */
	public static function shutdownHandler ()
	{
		$e = error_get_last ();
		if ($e)
		{
			self::errorHandler ($e ['type'], $e ['message'], $e ['file'],
				$e ['line']);
		}
	}

	/**
	 * @desc Отображение в лог нового события.
	 * @param mixed $text Отладочная информация.
	 * @param string|integer $type Тип события.
	 */
	public static function log ($text, $type = 'log')
	{
		if (is_numeric ($type))
		{
			$error_type_convertor = array (
				E_WARNING		=> 'warn',
				E_USER_WARNING	=> 'warn',
				E_ERROR			=> 'error',
				E_USER_ERROR	=> 'error',
				E_NOTICE		=> 'log',
				E_USER_NOTICE	=> 'log'
			);

			if (isset ($error_type_convertor [$type]))
			{
				$type = $error_type_convertor [$type];
			}
			else
			{
				$type = 'log';
			}
		}
		$time = date ('Y-m-d H:i:s');
		$text = is_scalar ($text) ? $text : var_export ($text, true);

		// В стандартный лог
		if (self::$config ['phplog'])
		{
			error_log ($text . PHP_EOL, E_USER_ERROR, 3);
		}

		// В файл
		if (self::$config ['file_active'])
		{
			if (isset (self::$config ['file_' . $type]))
			{
				$f = self::$config ['file_' . $type];
			}
			else
			{
				$f = self::$config ['file_log'];
			}

			if ($f)
			{
				$fh = fopen ($f, 'ab');
				fwrite ($fh, "$time $type $text");
				fclose ($fh);
			}
		}

		// В базу
		if (self::$config ['database_active'] && DDS::inited ())
		{
			DDS::execute (
				Query::instance ()
				->insert (self::$config ['database_table'])
				->values (array (
					'time'	=> $time,
					'where'	=> '',
					'text'	=> $text,
					'type'	=> substr ($type, 0, 6)
				))
			);
		}

		// FirePHP
		$limit = self::$config ['firebug_messages_limit'];
		if (
			self::$config ['firebug_active'] &&
			(self::$debug_messages_count++ < $limit) &&
			function_exists ('fb') &&
			!headers_sent ()
		)
		{
			fb ($text, $type);
		}

		// В браузер
		if (self::$config ['echo_active'])
		{
			echo "<pre>$type $text</pre>";
		}
	}

	/**
	 * @desc Отображение в лог значения переменной.
	 * @param mixed $var Переменная.
	 * @param string $name Имя переменной.
	 */
	public static function logVar ($var, $name = '')
	{
		if (empty ($name))
		{
			self::log (print_r ($var, true));
		}
		else
		{
			self::log ($name . ' => ' . print_r ($var, true));
		}
	}
	/**
	 * @desc вывод в лог времени загрузки фаилов
	 * @author Eriomin Ivan
	 * @tutorial
	 *	include $engine_dir . '/includes/FirePHPCore/fb.php';
	 *	Debug::microtime ('some special message');
	 */
	public static function microtime ()
	{
		$trace = array_slice (debug_backtrace (), 0, 1);
		$text = $trace [0]['file'] . '@' . $trace [0]['line'] . ': ';

		$now = microtime (true);
		$text .= round ($now - self::$lastTime, 5);
		self::$lastTime = $now;

		if (func_num_args ())
		{
			$text .= ' - ' . implode (', ', func_get_args ());
		}

		if (function_exists ('fb') && !headers_sent ())
		{
			fb ($text);
		}
		else
		{
			echo $text;
		}
	}

	/**
	 * @desc вывод в лог времени загрузки фаилов.
	 * @author Yury Shvedov
	 * @tutorial
	 *	include $engine_dir . '/includes/FirePHPCore/fb.php';
	 *	Debug::microtimeTotal ('some special message');
	 */
	public static function microtimeTotal ()
	{
		$trace = array_slice (debug_backtrace (), 0, 1);
		$text = $trace [0]['file'] . '@' . $trace [0]['line'] . ': ';

		$text .= round (microtime (true) - self::$startTime, 5);

		if (func_num_args ())
		{
			$text .= ' - ' . implode (', ', func_get_args ());
		}

		if (function_exists ('fb') && !headers_sent ())
		{
			fb ($text);
		}
		else
		{
			echo $text;
		}
	}

}

Debug::$startTime = microtime (true);
Debug::$lastTime = microtime (true);