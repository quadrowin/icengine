<?php

/**
 * Фабрика схем модели
 * 
 * @author morph
 * @package Ice\Orm
 * @Service("modelMapperScheme")
 */
class Model_Mapper_Scheme
{
	/**
	 * Получить схему по имени
	 * 
     * @param string $name
	 * @return Model_Mapper_Scheme_Abstract
	 */
	public static function byName($name)
	{
		$class_name = 'Model_Mapper_Scheme_' . $name;
		return new $class_name;
	}
}