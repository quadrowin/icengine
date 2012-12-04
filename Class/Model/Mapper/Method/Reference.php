<?php

/**
 * @desc Создание схемы ссылки
 */
class Model_Mapper_Method_Reference extends Model_Mapper_Method_Abstract
{
	/**
	 * @see Model_Mapper_Method_Abstract::execute
	 */
	public function execute ()
	{
        $serviceLocator = IcEngine::serviceLocator();
        $modelMapperSchemePart = $serviceLocator->getService(
            'modelMapperSchemePart'
        );
		$part = $modelMapperSchemePart->byName('Reference');
		return $part->set (
			$this->_params[0],
			isset ($this->_params[1]) ? $this->_params[1] : null,
			isset ($this->_params[2]) ? $this->_params[2] : null);
	}
}