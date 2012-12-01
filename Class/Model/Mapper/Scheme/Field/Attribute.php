<?php

/**
 * @desc Фабрика атрибутов полей схемы связей модели
 * @author Илья Колесников
 */
class Model_Mapper_Scheme_Field_Attribute
{
	/**
	 * @desc Получить атрибут поля по имени
	 * @param string $name
	 * @return Model_Mapper_Scheme_Field_Attribute_Abstract
	 */
	public static function byName ($name)
	{
		$serviceLocator = IcEngine::serviceLocator();
        $loader = $serviceLocator->getService('loader');
        $class_name = 'Model_Mapper_Scheme_Field_Attribute_' . $name;
		if (!$loader->load($class_name))
		{
			throw new Model_Mapper_Scheme_Field_Attribute_Exception ('Field had not found');
		}
		return new $class_name;
	}
}