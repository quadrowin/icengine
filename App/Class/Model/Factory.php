<?php

namespace Ice;

/**
 *
 * @desc Модель, необходимая для организации фабрик.
 * Используется в случаях, когда модели могут быть реализованы
 * разными классами.
 * @author Юрий Шведов
 * @package Ice
 *
 */
class Model_Factory extends Model
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
			// check namespace
			$p = strrpos ($object ['name'], '\\');
			if ($p)
			{
				$p2 = strrpos ($model, '\\');

				return
					substr ($object ['name'], 0, $p) .
					substr ($model, $p2) . '_' .
					substr ($object ['name'], $p + 1);
			}
			// without namespace
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