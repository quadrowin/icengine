<?php
/**
 * 
 * Класс для работы с изображениями
 * @author Юрий
 *
 */
class Helper_Image
{
	
	/**
	 * 
	 * @desc Префикс для оригинала изображения. 
	 * 	Будет создан в любом случае, не смотря на конфиг
	 * @var string
	 */
	const ORIGINAL = 'original';
	
	/**
	 * Аттрибут, где перечислены возможные типы подгружаемых изображений.
	 * @var string
	 */
	const TEMP_CONTENT_ATTRIBUTE = 'images';
	
	/**
	 * Загружен ли конфиг
	 * @var boolean
	 */
	protected static $_configLoaded = false;
	
	/**
	 * Код ошибки, возникшей в процессе обработки изображения.
	 * @var integer
	 */
	public static $code;
	
	/**
	 * 
	 * @var array
	 * @desc Конфиг для миниатюрок
	 * @tutorial 
	 * 	$ob->config = array (
	 * 		'sizings'	=> array (
	 * 			'avatar'	=> array (
	 * 				'sizes'	=> array (
	 * 					'small'	=> array (
	 * 						'width'	    => 50,
	 * 						'height'	=> 80,
	 * 						'crop'		=> true
	 * 					),
	 * 					'big'	=> array (
	 *						'width'	    => 160,
	 *						'height'    => 200,
	 *						'crop'	    => true
	 *					)
	 *				)
	 *			),
	 *			'gallery_image'	=> array (
	 *				'attributes'	=> array (
	 *					'title'	        => 'string',
	 *					'description'	=> 'string'
	 *				),
	 *				'sizes'	=> array (
	 *					'thumb'	    => array (
	 *						'width'	    => 190,
	 *						'height'	=> 140,
	 *						'crop'		=> true
	 *					),
	 *				)
	 *			)
	 *		)
	 *  );
	 */
	public static $config = array (
		'upload_path'	=> 'uploads/',
		'upload_url'	=> '/uploads/',
		'types'			=> array ()
	);
	
	/**
	 * 
	 * @var string
	 */
	const TEMP_PATH = 'images/tmp/';
	
	/**
	 * @desc Шаблон для имени файла
	 * @var string
	 */
	public static $template = '{name}/{prefix}/{key}.{ext}';
	
	/**
	 * 
	 * @param string $path
	 * @param array $values
	 * @return string
	 */
	protected static function _filename ($path, array $values = array ())
	{
		$filename = self::$template;
		foreach ($values as $key => $value)
		{
			$filename = str_replace ('{' . $key . '}', $value, $filename);
		}
		$path = rtrim ($path, '/') . '/' . $filename;
		$dir = dirname ($path);
		if (!is_dir ($dir))
		{
			mkdir ($dir, 0777, true);
			chmod ($dir, 0777);
		}
		return $path;
	}
	
	/**
	 * 
	 * @param string $type
	 * @return array
	 */
	protected static function _sizing ($type)
	{
		self::initConfig ();
		
		return 
			(isset (self::$config ['types']) && isset (self::$config ['types'][$type])) ?
			self::$config ['types'][$type]->asArray() :
			array ();
	}
	
	public static function initConfig ()
	{
		if (self::$_configLoaded)
		{
			return;
		}
		
		self::$_configLoaded = true;
		self::$config = Config_Manager::get (__CLASS__, self::$config);
	}
	
    /**
     * Инициализация временного контента для поддержки загрузки изображений
     * @param Temp_Content $tc
     * 		Временный контенто.
     * @param array|string $types
     * 		Наименования типов из конфига.
     */
    public static function initTempContent (Temp_Content $tc, $types)
    {
    	self::initConfig ();
    	$types = (array) $types;
    	
    	$tc_types = array ();
    	foreach ($types as $type)
    	{
    		if (isset (self::$config ['types'][$type]))
    		{
    			$tc_types [$type] = self::$config ['types'][$type];
    		}
    	}
    	
    	$tc->attr (self::TEMP_CONTENT_ATTRIBUTE, $tc_types);
    }
	
	/**
	 * Загрузка изображения для временного контента.
	 * @param Temp_Content $tc
	 * @param string $type
	 * @return null|Component_Image
	 */
	public static function upload (Temp_Content $tc, $type = null)
	{
		self::$code = 200;
		
		$sizings = $tc->attr (self::TEMP_CONTENT_ATTRIBUTE);
				
		if ($sizings && is_array ($sizings))
		{
			if ($type && isset ($sizings [$type]))
			{
				$sizing = $sizings [$type];
			}
			else
			{
				$sizing = reset ($sizings);
				$type = key ($sizings);
			}
		}
		
		if (!isset ($sizing))
		{
			self::$code = 400;
			Loader::load ('Zend_Exception');
			throw new Zend_Exception ('Type unsupported.', 400);
			return;
		}
		
		$sizing = array_merge (self::_sizing ($type), $sizing);
		
		return self::uploadSimple ($tc->table (), $tc->key (), $type, $sizing);
	}
	
	/**
	 * Простая загрузка изображения.
	 * @param string $table
	 * @param integer $row_id
	 * @param string $type
	 * @param array $sizing
	 * @return Component_Image|null
	 */
	public static function uploadSimple ($table, $row_id, $type, $sizing = null)
	{
		$file = Request::fileByIndex (0);
		
		if (!$file)
		{
			self::$code = 400;
			Loader::load ('Zend_Exception');
			throw new Zend_Exception ('File not received.', 400);
			return null;
		}
		
		if (!$sizing)
		{
			$sizing = self::_sizing ($type);
		}
		
		Loader::load ('Component_Image');
		$image = new Component_Image (array (
			'table'			=> $table,
			'rowId'			=> $row_id,
			'date'			=> Helper_Date::toUnix (),
			'name'			=> $type,
			'author'		=> '',
			'text'			=> '',
			'largeUrl'		=> '',
			'smallUrl'		=> '',
			'originalUrl'	=> '',
			'User__id'		=> User::id ()
		));
		$image->save ();
		$dst_path = self::$config ['upload_path'];
		
		$original = self::_filename (
			$dst_path,
			array (
				'name'		=> $type,
				'key'		=> $image->key (),
				'prefix'	=> self::ORIGINAL,
				'ext'		=> $file->extension
			)
		);
		
   		if (!$file->save ($original))
		{
			self::$code = 500;
			Loader::load ('Zend_Exception');
			throw new Zend_Exception ('Unable to move uploaded file.', 500);
			return null;
		}
   		
   	 	$info = getimagesize ($original);
		
		if (!$info)
		{
			self::$code = 400;
			unlink ($original);
			$image->delete ();
			
			Loader::load ('Zend_Exception');
			throw new Zend_Exception ('Unable to get image size.', 400);
			return null;
		}
		
		Loader::load ('Helper_Image_Resize');
   	 	$info = Helper_Image_Resize::resize (
   	 		$original, $original,
   	 		$info [0], $info [1]
   	 	);
   	 	
		if (!$info)
		{
			self::$code = 400;
			unlink ($original);
			$image->delete ();
			
			Loader::load ('Zend_Exception');
			throw new Zend_Exception ('Unable to change image size.', 400);
			return null;
		}
		
		$filenames = array ();
		
		if (!empty ($sizing ['sizes']))
		{
			$created = array ();
			
			foreach ($sizing ['sizes'] as $prefix => $size)
			{
				$filename = self::_filename (
					$dst_path,
					array (
						'name'		=> $type,
						'key'		=> $image->key (),
						'prefix'	=> $prefix,
						'ext'		=> $file->extension
					)
				);
				
				$thumb = Helper_Image_Resize::resize (
					$original, 
					$filename, 
					min ($info [0], $size ['width']), 
					min ($info [1], $size ['height']),
					isset ($size ['proportional']) ? $size ['proportional']: true, 
					isset ($size ['crop']) ? $size ['crop'] : false,
					isset ($size ['fit']) ? $size ['fit']: false 
				);
				
				if (!$thumb)
				{
					foreach ($filenames as $fn)
					{
						unlink ($fn);
					}
					$image->delete ();
					Loader::load ('Zend_Exception');
					throw new Zend_Exception ('Unable to change image size.', 400);
					return null;
				}
				$filenames [$prefix] = $filename;
			}
		}
		
		$attributes = array ();
		
		if (!empty ($sizing ['attributes']))
		{
			foreach ($sizing ['attributes'] as $key => $v)
			{
				$attributes [$key] = Request::post ('attr_' . $key);
			}
		}
		
		$sizing ['sizes'] [self::ORIGINAL] = array (
			'width'		=> $info [0],
			'height'	=> $info [1]
		);
		
		$filenames [self::ORIGINAL] = $original;
		
		$i = 0;
		foreach ($sizing ['sizes'] as $key => $size)
		{
			$tmp = array (
				$key . 'Url'	=> str_replace(self::$config['upload_path'], self::$config['upload_url'], $filenames [$key]),
				$key . 'Width'	=> $size ['width'],
				$key . 'Height'	=> $size ['height']
			);
			$attributes = array_merge ($attributes, $tmp);
			$i++;
		}
		
		$image->attr ($attributes);
		
		return $image;
	}
	
}