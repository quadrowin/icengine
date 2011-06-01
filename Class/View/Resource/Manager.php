<?php
/**
 * 
 * @desc Менеджер ресурсов представления
 * @author Юрий
 * @package IcEngine
 * 
 */
class View_Resource_Manager extends Manager_Abstract
{
	
	/**
	 * @desc Тип ресурса - CSS.
	 * Файл стилей.
	 * @var string
	 */
	const CSS = 'css';
	
	/**
	 * Тип ресурса - JS.
	 * Файл javascript.
	 * @var string
	 */
	const JS = 'js';
	
	/**
	 * @desc Тип ресурса - JTPL.
	 * Шаблоны для javascript.
	 * @var string
	 */
	const JTPL = 'jtpl';
	
	protected static $_config = array ();
	
	/**
	 * @desc Ресурсы.
	 * @var array <View_Resource_Item>
	 */
	protected static $_resources = array ();
	
	/**
	 * @desc Упаковщики ресурсов.
	 * @var array <View_Resrouce_Packer_Abstract>
	 */
	protected static $_packers = array ();
	
	/**
	 * @desc Добавление ресурса
	 * @param string|array $data
	 * 		Ссылка на ресурс или массив пар (тип => ссылка)
	 * @param string $type [optional] Тип ресурса
	 * @param array $flags Параметры
	 */
	public static function add ($data, $type = null, array $options = array ())
	{
		if (is_array ($data))
		{
			foreach ($data as $d)
			{
				self::add ($d, $type, $options);
			}
		}
		else
		{
			if (is_null ($type))
			{
				$type = strtolower (substr (strrchr ($data, '.'), 1));
			}
			
			if (!isset (self::$_resources [$type]))
			{
				self::$_resources [$type] = array ();
			}
			else 
			{
				foreach (self::$_resources [$type] as &$exists)
				{
					if ($exists->href == $data)
					{
						return;
					}
				}
			}
			
			$options ['href'] = $data;
			self::$_resources [$type][] = new View_Resource ($options);
		}
	}
	
	/**
	 * @desc Возвращает ресурсы указанного типа.
	 * @param string $type Тип
	 * @return array Ресурсы
	 */
	public static function getData ($type)
	{
		if (!isset (self::$_resources [$type]))
		{
			return array ();
		}
		
		return self::$_resources [$type];
	}
	
	/**
	 * @desc Возвращает упаковщик ресурсов для указанного типа.
	 * @param string $type
	 * @return View_Resource_Packer_Abstract
	 */
	public static function packer ($type)
	{
		if (!isset (self::$_packers [$type]))
		{
			$class = 'View_Resource_Packer_' . ucfirst ($type);
			Loader::load ($class);
			self::$_packers [$type] = new $class ();
		}
		return self::$_packers [$type];
	}
	
}

Loader::load ('View_Resource');