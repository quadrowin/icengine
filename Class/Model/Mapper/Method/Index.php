<?php

Loader::load ('Model_Mapper_Method_Abstract');

/**
 * @desc Создание схемы индекса
 */
class Model_Mapper_Method_Index extends Model_Mapper_Method_Abstract
{
	/**
	 * @see Model_Mapper_Method_Abstract::execute
	 */
	public function execute ()
	{
		Loader::load ('Model_Mapper_Scheme_Part');
		$part = Model_Mapper_Scheme_Part::byName ('Index');
		return $part->set (
			$this->_params [0],
			$this->_params [1]);
	}
}