<?php

/**
 * Менеджер модулей
 *
 * @author morph, neon
 */
class Module_Manager extends Manager_Abstract
{
    /**
     * @inheritdoc
     */
    protected $config = array(
        'configPath'        => 'Config',
        'defaultModule'     => 'Ice',
        'fromModel'         => false,
        'loaderPaths'       => array(
            'Class'         => 'Class',
            'Model'         => 'Class',
            ''              => 'Class',
            'Controller'    => 'Controller',
            'includes'      => 'includes'
        ),
        'modules'           => array(),
        'viewPath'          => 'View'
    );

	/**
	 * Текущие инициализированные модули
	 *
	 * @var array
	 */
	protected $modules = array();

	/**
	 * Добавить модуль в проект
	 *
     * @param string $moduleName Название модуля
	 */
	public function addModule($moduleName)
	{
		if (isset($this->modules[$moduleName])) {
			return;
		}
        $selfConfig = $this->config();
        $moduleConfig = $this->getConfig($moduleName);
        if ($moduleConfig->baseDir) {
            $moduleDir = $moduleConfig->baseDir;
            if ($moduleDir[0] != '/') {
                $moduleDir = IcEngine::root() . trim($moduleConfig->baseDir) .
                    '/';
            }
        } else {
            $moduleDir = IcEngine::root() . $moduleName . '/';
        }
        $loaderPaths = array();
        if ($selfConfig->loaderPaths) {
             $loaderPaths = $selfConfig->loaderPaths->__toArray();
        } else {
             $loaderPaths = array();
        }
        if ($moduleConfig->loaderPaths) {
            foreach ($moduleConfig->loaderPaths as $prefix => $value) {
                $loaderPaths[$prefix] = $value;
            }
        }
        if ($loaderPaths) {
            $pathTypes = array();
            foreach ($loaderPaths as $prefix => $value) {
                $pathTypes[$value][] = $prefix = !$prefix || $prefix[0] != '/'
                    ? $moduleDir . $prefix : $prefix;
            }
            $loader = $this->getService('loader');
            foreach ($pathTypes as $type => $paths) {
                foreach ($paths as $path) {
                    $loader->addPath($type, rtrim($path, '/') . '/');
                }
            }
        }
        $configPath = null;
        if (isset($moduleConfig->configPath)) {
            $configPath = $moduleConfig->configPath;
        } else {
            $configPath = $moduleName . '/' .
                ltrim($selfConfig->configPath, '/');
        }
        if (!empty($configPath) && $configPath[0] != '/') {
            $configPath = IcEngine::root() . ltrim($configPath, '/');
        }
        $configManager = $this->getService('configManager');
        if ($configPath) {
            $configManager->addPath(rtrim($configPath, '/') . '/');
        }
        if ($moduleName != $selfConfig->defaultModule) {
            $configManager->addPath(
                IcEngine::root() . $selfConfig->defaultModule .
                '/Config/Module/' . $moduleName . '/'
            );
        }
        $viewPath = null;
        if (isset($moduleConfig->viewPath)) {
            $viewPath = $moduleConfig->viewPath;
        } else {
            $viewPath = $moduleName . '/' . ltrim($selfConfig->viewPath, '/');
        }
        if (!empty($viewPath) && $viewPath[0] != '/') {
            $viewPath = IcEngine::root() . ltrim($viewPath, '/');
        }
        if ($viewPath) {
            $viewRenderManager = $this->getService('viewRenderManager');
            $view = $viewRenderManager->getView();
            $view->addTemplatesPath(rtrim($viewPath, '/') . '/');
        }
		$this->modules[$moduleName] = true;
	}

    /**
     * Получить конфиг для модуля
     *
     * @author morph
     */
    public function getConfig($moduleName)
    {
        $resourceKey = $this->resourceKey($moduleName);
        $resourceManager = $this->getService('resourceManager');
        $tryConfig = $resourceManager->get('Config', $resourceKey);
        if ($tryConfig) {
            return $tryConfig;
        }
        $baseConfigFile = IcEngine::root() . $moduleName . '/Config/Index.php';
        $selfConfig = self::config();
        $defaultModule = $selfConfig->defaultModule;
        $resultConfig = array();
        if (is_file($baseConfigFile)) {
            $config = include_once($baseConfigFile);
            if ($config) {
                $resultConfig = $config;
                unset($config);
            }
        }
        $overrideConfigFile = IcEngine::root() . $defaultModule .
            '/Config/Module/' . $moduleName . '.php';
        if (is_file($overrideConfigFile)) {
            include_onec($overrideConfigFile);
            if (isset($config)) {
                $resultConfig = array_merge($resultConfig, $config);
            }
        }
        $config = new Config_Array($resultConfig);
        if (!empty($resultConfig)) {
            $resourceManager->set('Config', $resourceKey, $config);
        }
        return $config;
    }

    /**
     * Загрузка модулей
     */
	public function init()
	{
        $config = $this->config();
        if ($config->fromModel) {
            $this->loadFromModel();
        } else {
            $this->loadByNames((array) $config->modules);
        }
	}

    /**
     * Загрузить модули по именам
     *
     * @param array $names
     */
    public function loadByNames($names)
    {
        foreach ($names as $name) {
			if (empty($name)) {
				continue;
			}
			self::addModule($name);
		}
    }

    /**
     * Загрузить модули из модели
     */
    public function loadFromModel()
    {
        $collectionManager = $this->getService('collectionManager');
        $moduleCollection = $collectionManager->create('Module')
            ->addOptions(array(
               'name'   => '::Order_Desc',
               'field'  => 'id'
            ));
		foreach ($moduleCollection->raw() as $module) {
			$this->addModule($module['name']);
		}
    }

    /**
     * Получить ключ ресурса
     *
     * @param string $moduleName
     * @return string
     */
    public function resourceKey($moduleName)
    {
        return 'Module/' . $moduleName;
    }
}