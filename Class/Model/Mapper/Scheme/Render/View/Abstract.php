<?php

/**
 * Абстрактное представление рендера схемы связей модели
 * 
 * @author morph
 * @package Ice\Orm
 */
class Model_Mapper_Scheme_Render_View_Abstract extends Manager_Abstract
{
	/**
	 * Конфигурация
	 * 
     * @var array
	 */
	protected $config = array(
		'models'	=> array(
			'default'	=> array()
		)
	);

	/**
	 * Получить имя схемы
	 * 
     * @return array
	 */
	public function getName()
	{
		return substr(
            get_class($this), strlen('Model_Mapper_Scheme_Render_View_')
        );
	}

	/**
	 * Получить отрендеренные части схемы для рендеринга
	 * 
     * @param Model_Mapper_Scheme_Abstract $scheme
	 * @return string
	 */
	public function getParts($mapperScheme)
	{
		$model = $mapperScheme->getModel();
		$modelName = $model->modelName();
		$mapperSchemeConfig = $mapperScheme->config();
        if (!isset($mapperSchemeConfig->$modelName)) {
            $mapperSchemeConfig = $mapperSchemeConfig->default;
        } else {
            $mapperSchemeConfig = $mapperSchemeConfig->$modelName;
        }
		$parts = array();
		$states = $mapperScheme->states();
		if (!$states) {
			return;
		}
        $serviceLocator = IcEngine::serviceLocator();
        $schemeRender = $serviceLocator->getService('modelMapperSchemeRender');
		foreach ($states as $name => $state) {
			$factoryName = $state->getValue()->factoryName();
			if (!isset($parts[$factoryName])) {
				$parts[$factoryName] = array();
			}
			$render = $schemeRender->byArgs(
				$mapperSchemeConfig->translator,
				$factoryName,
				$state->getValue()->getName()
			);
			$result = $render->render($state);
			$parts[$factoryName][$name] = $result;
		}
		return $parts;
	}

	/**
	 * Отрендерить схему модели
	 * 
     * @param Model_Mapper_Scheme_Abstract $scheme
	 * @return string
	 */
	public static function render($scheme)
	{

	}
}