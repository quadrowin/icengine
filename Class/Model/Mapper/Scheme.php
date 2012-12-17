<?php

/**
 * @desc Фабрика схем модели
 * @author Илья Колесников
 * @Service("modelMapperScheme")
 */
class Model_Mapper_Scheme
{
	/**
	 * @desc Получить схему по имени
	 * @param string $name
	 * @return Model_Mapper_Scheme_Abstract
	 */
	public static function byName ($name)
	{
		$class_name = 'Model_Mapper_Scheme_' . $name;
		return new $class_name;
	}
}