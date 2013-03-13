<?php

/**
 * Метод схемы связей модели, возвращающий пустую коллекцию моделей
 * 
 * @author morph
 * @package Ice\Orm
 */
class Model_Mapper_Method_Collection extends Model_Mapper_Method_Abstract
{
	/**
	 * Получить коллекцию
	 * 
     * @return Model
	 */
	public function get()
	{
        $serviceLocator = IcEngine::serviceLocator();
        $collectionManager = $serviceLocator->getService('collectionManager');
		return $collectionManager->create($this->params[0]);
	}
}