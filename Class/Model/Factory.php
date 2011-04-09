<?php
/**
 * 
 * @desc Модель, необходимая для организации фабрик.
 * Используется в случаях, когда модели могут быть реализованы 
 * разными классами.
 * @author Юрий Шведов
 * @package IcEngine
 *
 */
class Model_Factory 
{
	
	/**
	 * @desc Возвращает название класса, который будет использоваться 
	 * в качестве модели.
	 * @param string $model Название модели.
	 * @param string $key Первичный ключ.
	 * @param array $object Имеющиеся данные об объекте.
	 * @return string Название класса модели.
	 */
	public function delegateClass ($model, $key, $object)
	{
	    if (is_array ($object) && isset ($object ['name']))
	    {
		    return $model . '_' . $object ['name'];
	    }
	    
		return $model . '_' . DDS::execute (
		    Query::instance ()
			    ->select ('name')
			    ->from ($this->table ())
			    ->where ('id=?', $key)
		)->getResult ()->asValue ();
	}
	
	/**
	 * @desc Возвращает таблицу
	 * @return string
	 */
	public function table ()
	{
		return get_class ($this);
	}
	
}