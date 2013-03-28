<?php

/**
 * Создание схемы индекса
 * 
 * @author morph 
 * @package Ice\Orm
 */
class Model_Mapper_Method_Index extends Model_Mapper_Method_Abstract
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
		$part = $modelMapperSchemePart->byName('Index');
		return $part->set($this->params[0], $this->params[1]);
	}
}