<?php
/**
 * 
 * @desc Генерация уникальный идентификаторов.
 * @author Юрий
 * @package IcEngine
 *
 */
class Helper_Unique
{
	
	/**
	 * Счетчик для избежания генерации одинаковых ID в рамках
	 * одного процесса.
	 * @var integer
	 */
	private static $_counter = 0;
	
	/**
	 * Генерирует уникальный идентификатор на основе названия модели
	 * или текущего времени.
	 * @param Model $model
	 * @return string
	 */
	public static function forModel (Model $model)
	{
		$ext = 1000 + self::$_counter++;
		return $model->modelName () . uniqid (__CLASS__, true) . $ext;
	}
	
}