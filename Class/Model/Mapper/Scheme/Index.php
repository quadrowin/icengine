<?php

/**
 * Фабрика индексов схемы моделей
 * 
 * @author morph
 * @package Ice\Orm
 * @Service("modelMapperSchemeIndex")
 */
class Model_Mapper_Scheme_Index
{
	/**
	 * Получить индекс по имени
	 * 
     * @param string $name
	 * @return Model_Mapper_Scheme_Index_Abstract
	 */
	public function byName($name)
	{
		$className = 'Model_Mapper_Scheme_Index_' . $name;
		return new $className;
	}
}