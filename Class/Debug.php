<?php

function internal_error_handler_hide ($errno, $errstr, $errfile, $errline)
{
	//echo '['.$errno.':'.$errfile.'@'.$errline.'] '.$errstr."\n<br />";
	return true;
}

function internal_error_handler_ignore ($errno, $errstr, $errfile, $errline)
{
	return true;
}

function internal_exception_handler_ignore ($exception)
{
	echo "Uncaught exception: " , $exception->getMessage (), "\n";
}

/**
 * 
 * @desc Класс для отладки.
 * @author Гурус
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
		// Только firebug
		'fb'	=> array (
			'firebug_active'	=> false
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
		'echo_active'				=> true,
		
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
	
	public static $start_time = 0;
	
	/**
	 * @desc Скрытие всех возникающих ошибок.
	 */
	public static function disable ()
	{
		error_reporting (null);
		ini_set ('display_errors', false);
		ini_set ('html_errors', false);
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
				($errno == E_WARNING) &&
				strpos ($errfile, '/core.get_include_path.php') &&
				($errline == 35)
			) ||
			// Варнинг unlink
			(
				self::$config ['ignore_unlink_warning'] &&
				($errno = E_WARNING) &&
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
			'[' . $errno . ':' . $errfile . '@' . $errline . '] ' . 
			$errstr . "\r\n";
		
		foreach ($debug as $debug_step)
		{
			if (isset ($debug_step ['file']))
			{
				$log_text .= 
					'[' . $debug_step ['file'] . '@' . $debug_step ['line'] . ':' . 
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
			die ("<b>Terminated on fatal error.</b>");
		}
		
		return true;
	}
	
	/**
	 * @desc Включение внутреннего обработчика ошибок.
	 * @param array|Objective $config Настройки.
	 */
	public static function init ($config)
	{
		error_reporting (E_ALL);
		ini_set ('display_errors', true);
		ini_set ('html_errors', true);
		ini_set ('track_errors', true);

		$memory_start = function_exists ('memory_get_usage') ? memory_get_usage(true) : 0;
		
		if ($config)
		{
			self::setOptions ($config);
		}
		
		set_error_handler ("Debug::errorHandler");
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
			echo print_r ($arg, true);
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
					"=> \n"
				),
				'=&gt;',
				var_export ($var, true)
			) . "\n";
		}
		
		echo '</pre>';
	}
	
	public static function popErrorHandler ()
	{
		restore_error_handler ();
	}
	
	public static function popExceptionHandler ()
	{
		restore_exception_handler ();
	}
	
	/**
	 * @desc Установка внутреннего обработчика ошибок.
	 * @param string $type Тип обработчика.
	 */
	public static function pushErrorHandler ($type)
	{
		error_reporting (null);
		ini_set ('display_errors', false);
		ini_set ('html_errors', false);
		ini_set ('track_errors', false);
		
		set_error_handler ('internalErrorHandler_' . $type);
	}
	
	/**
	 * @desc Установка внутреннего обработчика исключений.
	 * @param string $type Тип обработчика.
	 */
	public static function pushExceptionHandler ($type)
	{
		set_exception_handler ('internal_exception_handler_' . $type);
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
		if (!$config)
		{
			self::setOutput ();
			return;
		}
		
		if (is_scalar ($config) && isset (self::$_configPresets [$config]))
		{
			$config = self::$_configPresets [$config];
		}
		
		if (is_object ($config) && $config instanceof Objective)
		{
			$config = $config->__toArray ();
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
	 *	Debug::microtime (__FILE__, __LINE__);
	 */
	public static function microtime()
	{
		$now = microtime (true);
		
		if (!self::$start_time)
		{
			self::$start_time = $now;
		}
		$text = func_num_args () ?
			(implode (':', func_get_args ()) . ':') : '';
			
		$text .= round($now - self::$start_time, 3) . ' second';
		
		if (function_exists ('fb') && !headers_sent ())
		{
			fb ($text);
		}
		else
		{
			echo round($now - self::$start_time, 3) . ' second';
		}
		
		self::$start_time = $now;
	}
	
}