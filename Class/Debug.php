<?php

/**
 * Внутренний обработчик ошибок
 * @param string $errno Код ошибки
 * @param string $errstr Текст ошибки
 * @param string $errfile Файл
 * @param string $errline Строка
 * @return boolean
 */
function internalErrorHandler_DebugClass ($errno, $errstr, $errfile, $errline)
{
	if (
		// Игнорим сообщение про open_basedir из smarty
		(
			Debug::$config ['ignore_open_basedir_warning'] &&
			($errno == E_WARNING) &&
			strpos ($errfile, '/core.get_include_path.php') &&
			($errline == 35)
		) ||
		// Варнинг unlink
		(
			Debug::$config ['ignore_unlink_warning'] &&
			($errno = E_WARNING) &&
			substr ($errstr, 0, 7) == 'unlink('
		)
	)
	{
		return false;
	}
	
	if (Debug::$config ['print_backtrace'])
	{
		echo '<pre>';
		debug_print_backtrace ();
		echo '</pre>';
	}
	
	$debug = array_slice (debug_backtrace (), 1, 10);
	Debug::removeUninterestingObjects ($debug);
	
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
	
	Debug::log ($log_text, $errno);

	if (
		($errno == E_ERROR || $errno == E_USER_ERROR) && 
		Debug::$config ['die_on_error']
	)
	{
		die ("<b>Terminated on fatal error.</b>");
	}
	
	return true;
}

function internalErrorHandler_hide ($errno, $errstr, $errfile, $errline)
{
	//echo '['.$errno.':'.$errfile.'@'.$errline.'] '.$errstr."\n<br />";
	return true;
}

function internalErrorHandler_ignore ($errno, $errstr, $errfile, $errline)
{
    return true;
}

function internal_exception_handler_ignore ($exception)
{
    echo "Uncaught exception: " , $exception->getMessage (), "\n";
}

class Debug
{
	
	const DEFAULT_TABLE = 'log';
	
	const ERROR_HANDLER_INNER = 'DebugClass';
	
	const ERROR_HANDLER_HIDE = 'hide';
	
	const ERROR_HANDLER_IGNORE = 'ignore';
	
	const EXCEPTION_HANDLER_IGNORE = 'ignore';
	
	public static $config = array (
	
		/**
		 * Ведение лога в БД
		 * @var array
		 */
		'database'					=> array (
			/**
			 * Активно
			 * @var boolean
			 */
			'active'	=> false,
			/**
			 * Таблица для ведения лога
			 * @var string
			 */
			'table'		=> self::DEFAULT_TABLE
		),
		
		/**
		 * Остановить выполнение скрипта при ошибке
		 * @var boolean
		 */
		'die_on_error'				=> true,
		
		/**
		 * Отображение в браузер
		 * @var boolean
		 */
		'echo'						=> array (
			'active'	=> true
		),
		
		/**
		 * Файлы для отображения
		 * @var string
		 */
		'files'						=> array (
			/**
			 * Активно
			 * @var boolean
			 */
			'active'	=> false,
			'ERROR'		=> 'error.txt',
			'WARN'		=> 'warning.txt',
			'LOG'		=> 'notice.txt'
		),
		
		/**
		 * Отображение в FireBug
		 * @var boolean
		 */
		'firebug'	=> array (
			/**
			 * Активно
			 * @var boolean
			 */
			'active'	            => false,
		
			/**
			 * Следим за количеством дебаг сообщений, чтобы 
			 * длина заголовка не превысила максимально допустимую,
			 * иначе на странице вместо текста будет "X-Wf-1-1-1-32: 201|...."
			 * 
			 * @var integer
			 */
			'messages_limit'		=> 11,
		),
		
		/**
		 * Игнорировать варнинг open basedir
		 * @var boolean
		 */
		'ignore_open_basedir_warning'	=> true,
		
		/**
		 * Игнорировать варнинг при unlink
		 * @var boolean
		 */
		'ignore_unlink_warning'			=> true,
		
		/**
		 * Вывод на экран трасировки
		 * @var boolean
		 */
		'print_backtrace'				=> false,
		
		/**
		 * Стандартный лог PHP
		 * @var boolean
		 */
		'phplog'						=> true
	);
	
	/**
	 * Количество сообщений
	 * @var integer
	 */
	public static $debug_messages_count = 0;
		
	/**
	 * Скрытие всех возникающих ошибок
	 */
	public static function disable ()
	{
		error_reporting (null);
		ini_set ('display_errors', false);
		ini_set ('html_errors', false);
		ini_set ('track_errors', true);
		
		set_error_handler ('internalErrorHandler_hide');
	}
	
	/**
	 * Включение внутреннего обработчика ошибок
	 * @param array|Config_Abstract $config
	 */
	public static function init ($config = array ())
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
		
		set_error_handler ("internalErrorHandler_DebugClass");
	}
	
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
	 * 
	 * @param mixed $var
	 * @param string $name
	 */
	public static function vardump ($var, $name = '')
	{
		echo '<pre>';
		
		if (!empty ($name))
		{
			echo $name . ' =&gt; ';
		}
		
		echo str_replace (
			array (
				"=>\n",
				"=> \n"
			),
			'=&gt;',
			var_export ($var, true)
		);
		
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
	
	public static function pushErrorHandler ($type)
	{
	    error_reporting (null);
		ini_set ('display_errors', false);
		ini_set ('html_errors', false);
		ini_set ('track_errors', false);
		
		set_error_handler ('internalErrorHandler_' . $type);
	}
	
	public static function pushExceptionHandler ($type)
	{
	    set_exception_handler ('internal_exception_handler_' . $type);
	}
	
	/**
	 * Удаление объекта БД из лога, иначе
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
	 * 
	 * @param array|Config_Abstract $config
	 */
	public static function setOptions ($config)
	{
		if (!$config)
		{
			self::setOutput ();
			return;
		}
		
		if (is_a ($config, 'Config_Abstract'))
		{
			$config = $config->__toArray ();
		}
		
		self::$config = self::arrayMergeReplaceRecursive (self::$config, $config);
//		
//		self::vardump (self::$config);
//		die();
	}
	
	/**
	 * Установки режимов отображения ошибок
	 * @param string|null $database Таблица БД
	 * @param boolean $echo Вывод в браузер
	 * @param boolean|null $firebug FireBug
	 * @param string|null $file Имя файла
	 */
	public static function setOutput ($database = null, $echo = true,
		$firebug = null, $file = null)
	{
		// БД
		if ((is_null ($database) && class_exists ('DDS')) || $database)
		{
			self::$config ['database']['active'] = (bool) $database;
		}
		else
		{
			self::$config ['database']['active'] = false;
		}
		
		// Браузер
		self::$config ['echo']['active'] = (bool) $echo;
		
		// FireBug
		self::$config ['firebug'] = (
			(is_null ($firebug) && function_exists ('fb')) ||
			$firebug
		);
		
		// Файл
		if (is_string ($file))
		{
			self::$config ['file'] = array (
				'active'	=> true,
				'ERROR'		=> $file,
				'WARN'		=> $file,
				'LOG'		=> $file
			);
		}
		else
		{
			self::$config ['file']['active'] = false;
		}
	}
	
	/**
	 * Отображение в лог нового события
	 * @param mixed $text Отладочная информация
	 * @param string|integer $type Тип события
	 */
	public static function log ($text, $type = 'LOG')
	{
		if (is_numeric ($type))
		{
			$error_type_convertor = array (
				E_WARNING		=> 'WARN',
				E_USER_WARNING	=> 'WARN',
				E_ERROR			=> 'ERROR',
				E_USER_ERROR	=> 'ERROR',
				E_NOTICE		=> 'LOG',
				E_USER_NOTICE	=> 'LOG'
			);
			
			if (isset ($error_type_convertor [$type]))
			{
				$type = $error_type_convertor [$type];
			}
			else
			{
				$type = 'LOG';
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
		if (self::$config ['files']['active'])
		{
			if (isset (self::$config ['files'][$type]))
			{
				$f = self::$config ['files'][$type];
			}
			else
			{
				$f = self::$config ['files']['LOG'];
			}
			
			if ($f)
			{
				$fh = fopen ($f, 'ab');
				fwrite ($fh, "$time $type $text");
				fclose ($fh);
			}
		}
		
		// В базу
		if (self::$config ['database']['active'] && DDS::inited ())
		{
			DDS::execute (
				Query::instance ()
				->insert (self::$config ['database']['table'])
				->values (array (
					'time'	=> $time,
					'where'	=> '',
					'text'	=> $text,
					'type'	=> substr ($type, 0, 6)
				))
			);
		}
		
		// FirePHP
		if (
			self::$config ['firebug']['active'] &&
			(self::$debug_messages_count++ < self::$config ['firebug']['messages_limit']) &&
			function_exists ('fb')
		)
		{
			fb ($text, $type);
		}
		
		// В браузер
		if (self::$config ['echo']['active'])
		{
			echo "<pre>$type $text</pre>";
		}
	}
	
	/**
	 * Отображение в лог значения переменной
	 * @param mixed $var Переменная 
	 * @param string $name Имя переменной
	 */
	public static function logVar ($var, $name = '')
	{
		if (empty ($name))
		{
			self::log (var_export ($var, true));
		}
		else
		{
			self::log ($name . ' => ' . var_export ($var, true));
		}
	}
	
	/**
	 * Merges any number of arrays of any dimensions, the later overwriting
	 * previous keys, unless the key is numeric, in whitch case, duplicated
	 * values will not be added.
	 *
	 * The arrays to be merged are passed as arguments to the function.
	 *
	 * @access public
	 * @return array Resulting array, once all have been merged
	 */
	public static function arrayMergeReplaceRecursive ()
	{
	    // Holds all the arrays passed
	    $params = &func_get_args ();
	   
	    // First array is used as the base, everything else overwrites on it
	    $return = array_shift ($params);
	   
	    // Merge all arrays on the first array
	    foreach ( $params as $array ) {
	        foreach ( $array as $key => $value ) {
	            // Numeric keyed values are added (unless already there)
	            if (is_numeric ( $key ) && (! in_array ( $value, $return ))) {
	                if (is_array ( $value )) {
	                    $return [] = self::arrayMergeReplaceRecursive ( $return [$key], $value );
	                } else {
	                    $return [] = $value;
	                }
	               
	            // String keyed values are replaced
	            } else {
	                if (isset ( $return [$key] ) && is_array ( $value ) && is_array ( $return [$key] )) {
	                    $return [$key] = self::arrayMergeReplaceRecursive ( $return [$key], $value );
	                } else {
	                    $return [$key] = $value;
	                }
	            }
	        }
	    }
	   
	    return $return;
	}
	
}