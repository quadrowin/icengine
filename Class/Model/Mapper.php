<?php

/**
 * @desc Реализация ORM
 * @author Илья Колесников
 */
class Model_Mapper
{
	/**
	 * @desc Конфигурация схемы связей моделей
	 * @var Objective
	 */
	protected static $_config;

	/**
	 * @desc Схемы связей моделей
	 * @var array
	 */
	private static $_schemes = array ();

	/**
	 * (non-PHPDoc)
	 */
	public static function __callStatic ($method, $params)
	{
        $serviceLocator = IcEngine::serviceLocator();
        $modelMapperMethod = $serviceLocator->getService('modelMapperMethod');
		$method = $modelMapperMethod->normalizaName($method);
		$method = $modelMapperMethod->byName($method);
		$method->setParams ($params);
		return $method->execute();
	}

	/**
	 * @desc Получить конфигурацию
	 * @return Objective
	 */
	public static function config()
	{
		$serviceLocator = IcEngine::serviceLocator();
        $configManager = $serviceLocator->getService('configManager');
        if (!is_object (self::$_config))
		{
			self::$_config = $configManager->get(__CLASS__, self::$_config);
		}
		return self::$_config;
	}

	/**
	 * @desc Получить схему для моделей
	 * @param string $model_name
	 */
	public static function scheme ($model)
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
		if (!isset (self::$_schemes[$key]))
		{
			$config = $configManager->get('Model_Mapper_' . $model_name);
			if (!$config)
			{
				return;
			}
            
			$scheme_name = 'Simple';
			if (isset ($config->scheme))
			{
				$scheme_name = $config->scheme;
			}
			$scheme = $modelMapperScheme->byName($scheme_name);
			$scheme->setModel($model);
			foreach ($config as $name => $values)
			{
				if ($values)
				{
					$scheme = $modelMapperSchemePart->getAuto(
						$name, $scheme, $values
					);
				}
			}
			self::$_schemes[$key] = $scheme;
		}
		return clone self::$_schemes[$key];
	}
}