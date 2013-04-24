<?php

/**
 * Абстрактная часть схемы моделей
 * 
 * @author morph
 * @package Ice\Orm
 */
class Model_Mapper_Scheme_Part_Abstract
{
	/**
	 * Спецификации
	 */
	protected static $specification;

	/**
	 * Заполнить часть схемы
	 * 
     * @param Model_Mapper_Scheme_Abstract $scheme
	 * @param Objective $values
	 * @return Model_Mapper_Scheme_Abstract
	 */
	public function execute($scheme, $values)
	{

	}

	/**
	 * Получить имя
	 * 
     * @return string
	 */
	public function getName()
	{
		return substr(get_class($this), strlen('Model_Mapper_Scheme_Part_'));
	}

	/**
	 * @desc Получить спецификацию
	 * @return string
	 */
	public function getSpecification()
	{
		return static::$specification;
	}
}