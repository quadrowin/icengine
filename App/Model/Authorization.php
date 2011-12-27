<?php

namespace Ice;

/**
 *
 * @desc Метод авторизации пользователя.
 * Фабрика для моделей авторизации. Такое разделение позволяет
 * для каждого пользователя реализовать несколько методов авторизации
 * (по логин, по емейлу, по телефону и т.п.)
 * @author Юрий Шведов
 * @package Ice
 *
 */
class Authorization extends Model_Factory
{

	/**
	 * @desc Возвращает модель авторизации по названию
	 * @param string $name Название модели авторизации
	 * @return Authorization_Abstract
	 */
	public static function byName ($name)
	{
		return Model_Manager::getInstance ()->byQuery (
			__CLASS__,
			Query::instance ()
				->where ('name', $name)
		);
	}

}

Loader::load ('Authorization_Abstract');