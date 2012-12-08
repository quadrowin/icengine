<?php

/**
 * Реализация ORM
 *
 * @author Илья Колесников, neon
 */
class Model_Mapper
{
	/**
	 * Конфигурация схемы связей моделей
	 * @var Objective
	 */
	protected $config;

	/**
	 * Схемы связей моделей
	 * @var array
	 */
	private $schemes = array();

	/**
	 * (non-PHPDoc)
	 */
	public static function __callStatic($method, $params)
	{
        $serviceLocator = IcEngine::serviceLocator();
        $modelMapperMethod = $serviceLocator->getService('modelMapperMethod');
		$method = $modelMapperMethod->normalizaName($method);
		$method = $modelMapperMethod->byName($method);
		$method->setParams($params);
		return $method->execute();
	}

	/**
	 * @desc Получить конфигурацию
	 * @return Objective
	 */
	public function config()
	{
		$serviceLocator = IcEngine::serviceLocator();
        $configManager = $serviceLocator->getService('configManager');
        if (!is_object($this->config)) {
			$this->config = $configManager->get(__CLASS__, $this->config);
		}
		return $this->config;
	}

	/**
	 * Получить схему для моделей
	 *
	 * @param string $model_name
	 */
	public function scheme($model)
	{
        $serviceLocator = IcEngine::serviceLocator();
        $configManager = $serviceLocator->getService('configManager');
        $modelMapperScheme = $serviceLocator->getService('modelMapperScheme');
        $modelMapperSchemePart = $serviceLocator->getService(
            'modelMapperSchemePart'
        );
		if (is_object($model)) {
			$model_name = $model->modelName();
		} else {
			$model = $model_name;
		}
		$key = $model_name . '_' . $model->key();
		if (!isset($this->schemes[$key])) {
			$config = $configManager->get('Model_Mapper_' . $model_name);
			if (!$config) {
				return;
			}
			$scheme_name = 'Simple';
			if (isset($config->scheme)) {
				$scheme_name = $config->scheme;
			}
			$scheme = $modelMapperScheme->byName($scheme_name);
			$scheme->setModel($model);
			foreach ($config as $name => $values) {
				if ($values) {
					$scheme = $modelMapperSchemePart->getAuto(
						$name, $scheme, $values
					);
				}
			}
			$this->schemes[$key] = $scheme;
		}
		return clone $this->schemes[$key];
	}
}