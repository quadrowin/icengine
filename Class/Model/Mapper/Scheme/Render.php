<?php

/**
 * Фабрика рендеров схемы моделей
 * 
 * @author morph
 * @package Ice\Orm
 * @Service("modelMapperSchemeRender")
 */
class Model_Mapper_Scheme_Render
{
	/**
	 * Получить рендер по параметрам
	 * 
     * @param string $translator_name Имя траслятор дата сорса
	 * @param string $factory_name Имя фабрики сущности
	 * @param string $name Имя сущности
	 * @return Model_Mapper_Scheme_Render_Abstract
	 */
	public function byArgs($translatorName, $factoryName, $name)
	{
		$values = array($translatorName, $factoryName, $name);
		foreach ($values as $i => $value) {
			if (!$value) {
				unset($values[$i]);
			}
		}
		return $this->byName(implode('_', $values));
	}

	/**
	 * Получить рендер по имени
	 * 
     * @param string $name
	 */
	public function byName($name)
	{
		$className = 'Model_Mapper_Scheme_Render_' . $name;
		return new $className;
	}
}