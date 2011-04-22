<?php
/**
 * 
 * @desc Абстрактный класс фильтра коллекции.
 * @author Юрий Шведов
 * @package IcEngine
 *
 */
class Model_Collection_Filter_Abstract
{
	
	/**
	 * @desc Поля, передаваемые в опцию
	 * @var array 
	 */
	public $fields = array (
		'id'	=> 'city_id'
	);
	
	/**
	 * @desc 
	 * @param Model_Collection $collection
	 * @param array $params
	 */
	protected function _filter (Model_Collection $collection, array $params)
	{
		$params ['name'] = $this->name ();
		$collection->addOptions ($params);
	}
	
	/**
	 * @desc Наложение фильтра на коллекцию.
	 * @param Model_Collection $collection Коллекция.
	 * @param Data_Transport $data Данные для фильтрации.
	 */
	public function filter (Model_Collection $collection, Data_Transport $data)
	{
		if (!$this->fields)
		{
			return $this->_filter ($collection, array ());
		}
		
		if (count ($this->fields) == 1)
		{
			reset ($this->fields);
			return $this->_filter (
				$collection,
				array (
					key ($this->fields) => $data->receive (current ($this->fields))
				)
			);
		}
		
		return $this->_filter (
			$collection,
			array_combine (
				array_keys ($this->fields),
				call_user_func_array (
					array ($data, 'receive'),
					array_values ($this->fields)
				)
			)
		);
	}
	
	/**
	 * @desc Возвращает название фильтра.
	 * @return string Название фильтра.
	 */
	public function name ()
	{
		return substr (get_class ($this), 24);
//		return substr (get_class ($this), strlen ('Model_Collection_Filter_'));
	}
	
}