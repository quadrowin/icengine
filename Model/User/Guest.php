<?php

/**
 * Модель гостя (незарегистрированного посетителя сайта).
 * 
 * @author goorus, morph
 */
class User_Guest extends User_Cli
{
	/**
	 * @desc Конфиг
	 * @var array
	 */
	protected static $config = array(
		/**
		 * @desc Конфиг пользователя
		 * @var array
		 */
		'fields'	=> array(
			'id'		=> 0,
			'active'	=> 1,
			'login'		=> '',
			'name'		=> '',
			'email'		=> '',
			'password'	=> ''
		)
	);
}