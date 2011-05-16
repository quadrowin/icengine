<?php
/**
 * 
 * @desc Опция коллекции.
 * @author Юрий Шведов, Илья Колесников
 * @package IcEngine
 *
 */
class Model_Collection_Option_Item
{
	
	/**
	 * @desc Название опции
	 * @var string
	 */
	protected $_name;
	
	/**
	 * @desc Параметры
	 * @var array
	 */
	protected $_params;
	
	/**
	 * @desc Опции
	 * @var Model_Collection_Option_Abstract
	 */
	protected $_option;
	
	/**
	 * @desc Создает и возвращает опцию
	 * @param string $name Название опции.
	 * @param array $params Параметры применения.
	 */
	public function __construct ($name, $params = array ())
	{
		$this->_name = $name;
		$this->_params = $params;
	}
	
	/**
	 * @desc Загрузка опцию.
	 * @param string $model_name Название модели для которой подключается
	 * опция.
	 * @param string $option Название опции. Может содержать название модели.
	 * Active - Опция Active текущей коллекции.
	 * Car::Active =- Car_Collection_Option_Active
	 * ::Active - Model_Collection_Option_Active
	 */
	private function _loadOption ($model_name, $option)
	{
		$p = strpos ($option, '::'); 
		if ($p === false)
		{
			$class = $model_name . '_Collection_Option_' . $option;
		}
		elseif ($p === 0)
		{
			$class = 'Model_Collection_Option_' . substr ($option, $p + 2);
		}
		else
		{
			$class = 
				substr ($option, 0, $p) . 
				'_Collection_Option_' .
				substr ($option, $p + 2);
		}
		
		Loader::load ($class);
		$this->_option = new $class ();
	}
	
	/**
	 * @desc Наложение опции.
	 * @param string $model_name
	 * @param string $before_after
	 * @param array $args
	 * @return mixed Результат наложения.
	 */
	public function execute ($model_name, $before_after, array $args)
	{
		if (!$this->_name)
		{
			return;	
		}
		
		if (!$this->_option)
		{
			$this->_loadOption ($model_name, $this->_name, $before_after);
		}
		
		Loader::load ('Executor');
		
		return Executor::execute (
			array ($this->_option, $before_after),
			$args
		);
	}
	
	/**
	 * @desc Получить имя опшина
	 * @return string
	 */
	public function getName ()
	{
		return $this->_name;
	}
	
	/**
	 * @desc Возвращает параметры опции.
	 * @return array
	 */
	public function getParams ()
	{
		return $this->_params;
	}
	
}