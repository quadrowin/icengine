<?php

/**
 * Создание схемы поля
 * 
 * @author morph
 * @package Ice\Orm
 */
class Model_Mapper_Method_Field extends Model_Mapper_Method_Abstract
{
	/**
     * @inheritdoc
	 * @see Model_Mapper_Method_Abstract::execute
	 */
	public function execute()
	{
        $serviceLocator = IcEngine::serviceLocator();
        $modelMapperSchemePart = $serviceLocator->getService(
            'modelMapperSchemePart'
        );
		$part = $modelMapperSchemePart->byName('Field');
		return $part->set(
			$this->params[0],
			isset($this->params[1]) ? $this->params[1] : array()
        );
	}
}