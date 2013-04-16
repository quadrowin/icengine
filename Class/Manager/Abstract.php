<?php

/**
 * Абстрактный класс менеджера
 *
 * @author morph, goorus
 */
abstract class Manager_Abstract
{
	/**
     * Конфигурация
     *
	 * @var array
	 */
	protected $config = array();

    /**
     * Локатор сервисов
     *
     * @var Service_Locator
     */
    protected $serviceLocator;

	/**
	 * Получить конфигурацию
	 *
     * @return Objective
	 */
	public function config()
	{
		if (is_array($this->config)) {
            $configManager = $this->getService('configManager');
			$config = $configManager->get(get_class($this), $this->config);
            if ($config) {
                $this->config = $config;
            } else {
                $this->config = new Objective(array());
            }
		}
		return $this->config;
	}
    
    /**
     * Получить услугу по имени
     *
     * @param string $serviceName
     * @return mixed
     */
    public function getService($serviceName)
    {
        if (!$this->serviceLocator) {
            $this->serviceLocator = IcEngine::serviceLocator();
        }
        return $this->serviceLocator->getService($serviceName);
    }

    /**
     * Получить текущий локатор сервисов
     *
     * @return Service_Locator
     */
    public function getServiceLocator()
    {
        return $this->serviceLocator;
    }

    /**
     * Изменить текущий локатор сервисов
     *
     * @param Service_Locator $serviceLocator
     */
    public function setServiceLocator($serviceLocator)
    {
        $this->serviceLocator = $serviceLocator;
    }
}