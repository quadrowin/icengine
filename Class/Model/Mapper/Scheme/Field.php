<?php

/**
 * Фабрика полей схемы моделей
 * 
 * @author morph
 * @Service("modelMapperSchemeField")
 */
class Model_Mapper_Scheme_Field
{
	/**
	 * Получить поле по имени
	 * 
     * @param string $name
	 * @return Model_Mapper_Scheme_Field_Abstract
	 */
	public function byName($name)
	{
		$className = 'Model_Mapper_Scheme_Field_' . $name;
		return new $className;
	}
}