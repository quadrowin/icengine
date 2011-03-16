<?php
/**
 * 
 * @desc Абстрактный класс для авторизаций.
 * @author Юрий Шведов
 * @package IcEngine
 *
 */
abstract class User_Authorization_Abstract extends Model_Factory_Delegate
{
	
	/**
	 * @desc Находит пользователя по данным с формы авторизации.
	 * @param array $data Данные с формы
	 * @return User|null Найденный пользователь или null.
	 */
	public abstract function findUser ($data);
	
}