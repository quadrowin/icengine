<?php

/**
 * @desc Simple
 * @author Shvedov_U
 * @package IcEngine
 */
class Data_Mapper_Simple extends Data_Mapper_Abstract
{

	/**
	 * @desc
	 * @param string $model
	 * @return string
	 */
	public function getTable($model)
	{
        $serviceLocator = IcEngine::serviceLocator();
		return $serviceLocator->getService('modelScheme')->table($model);
	}

	/**
	 * @desc
	 * @param string $table
	 * @return string
	 */
	public function getModel($table)
	{
        $serviceLocator = IcEngine::serviceLocator();
		return $serviceLocator->getService('modelScheme')->tableToModel($table);
	}
}