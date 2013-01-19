<?php

/**
 * Часть схемы моделей, отвечающая за поля
 * 
 * @author morph
 * @package Ice\Orm
 */
class Model_Mapper_Scheme_Part_Field extends Model_Mapper_Scheme_Part_Abstract
{
	/**
     * @inheritdoc
	 */
	protected static $specification = 'fields';

	/**
	 * Создать поле схемы модели
	 * 
     * @param string $name Название поля
	 * @param array $attributes Атрибуты
	 * @return Model_Mapper_Scheme_Field_Abstract
	 */
	public function set($name, $attributes = array())
	{
		$serviceLocator = IcEngine::serviceLocator();
        $modelMapperSchemeField = $serviceLocator->getService(
            'modelMapperSchemeField'
        );
        $modelMapperSchemeFieldAttribute = $serviceLocator->getService(
            'modelMapperSchemeFieldAttribute'
        );
        $field = $modelMapperSchemeField->byName($name);
		foreach ($attributes as $name => $value) {
			if (is_numeric($name)) {
				$name = $value;
				$value = null;
			}
			$attribute = $modelMapperSchemeFieldAttribute->byName($name);
			$attribute->setValue($value);
			$field->attributes()->add($attribute);
		}
		return $field;
	}

	/**
     * @inheritdoc
	 * @see Model_Mapper_Scheme_Part_Abstract::execute
	 */
	public function execute($scheme, $values)
	{
		foreach ($values as $name => $params) {
			$attributes = array();
			if (!empty($params[1])) {
				$attributes = $params[1] ? $params[1]->__toArray() : array();
			}
			$scheme->$name = $this->set($params[0], $attributes);
		}
		return $scheme;
	}
}