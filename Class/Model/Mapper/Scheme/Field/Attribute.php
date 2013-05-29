<?php

/**
 * Фабрика атрибутов полей схемы связей модели
 * 
 * @author morph
 * @package Ice\Orm
 * @Service("modelMapperSchemeFieldAttribute")
 */
class Model_Mapper_Scheme_Field_Attribute
{
	/**
	 * Получить атрибут поля по имени
	 * 
     * @param string $name
	 * @return Model_Mapper_Scheme_Field_Attribute_Abstract
	 */
	public static function byName ($name)
	{
        $className = 'Model_Mapper_Scheme_Field_Attribute_' . $name;
		return new $className;
	}
}