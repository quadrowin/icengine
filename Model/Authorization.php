<?php
/**
 *  Метод авторизации пользователя.
 * Фабрика для моделей авторизации. Такое разделение позволяет
 * для каждого пользователя реализовать несколько методов авторизации
 * (по логин, по емейлу, по телефону и т.п.)
 *
 * @author Юрий Шведов
 * @package IcEngine
 */
class Authorization extends Model_Factory
{
	/**
	 *  Возвращает модель авторизации по названию
	 *
	 * @param string $name Название модели авторизации
	 * @return Authorization_Abstract
	 */
	public static function byName ($name)
	{
		$query = $this->getService('query');
		return $this->getService('modelManager')->byQuery(
			__CLASS__,
			$query->where('name', $name)
		);
	}
}