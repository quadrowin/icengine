<?php

namespace Ice;

/**
 *
 * @desc Класс мэпера данных.
 * @author Илья Колесников
 * @package Ice
 *
 */
class Data_Mapper
{
	/**
	 * @desc Модели приложения
	 * @var Data_Mapper_Result
	 */
	protected static $_models;

	/**
	 * @desc Получить список моделей приложения
	 * @return Data_Mapper_Result
	 */
	public static function getModels ()
	{
		if (is_null (self::$_models))
		{
			Loader::load ('Data_Mapper_Result');
			self::$_models = new Data_Mapper_Result ();
		}

		return self::$_models;
	}
}