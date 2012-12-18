<?php

/**
 * Часть схемы моделей
 * 
 * @author morph
 * @Service("modelMapperSchemePart")
 */
class Model_Mapper_Scheme_Part extends Manager_Abstract
{
	/**
	 * Конфигурация
	 * 
     * @var array
	 */
	protected $config = array(
		'parts'	=> array (
			'Field',
			'Index',
			'Reference'
		)
	);

	/**
	 * Получить часть схемы по имени
	 * 
     * @param string $name
	 * @return Model_Mapper_Scheme_Part_Abstract
	 */
	public function byName($name)
	{
		$className = 'Model_Mapper_Scheme_Part_' . $name;
		return new $className;
	}

	/**
	 * Получить часть схемы по критериям
	 * 
     * @param string $name
	 * @param Model_Mapper_Scheme_Abstract $scheme
	 * @param Objective $values
	 * @return Model_Mapper_Scheme_Abstract
	 */
	public function getAuto($name, $scheme, $values)
	{
		$parts = $this->config()->parts;
		if (!$parts) {
			return;
		}
		foreach ($parts as $part) {
			$part = $this->byName($part);
			if ($part->getSpecification() == $name) {
				$part->execute($scheme, $values);
			}
		}
		return $scheme;
	}
}