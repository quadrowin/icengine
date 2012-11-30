<?php

/**
 * Базовый класс контроллера
 *
 * @author goorus, morph
 */
class Controller_Abstract
{

	/**
	 * Последний вызванный экшен. В случае, если был вызван replaceAction,
     * может отличаться от $_task
     *
	 * @var string
	 */
	protected $currentAction;

    /**
     * Локатор сервисов
     *
     * @var Service_Locator
     */
    protected static $serviceLocator;

	/**
	 * Текущая задача
     *
	 * @var Controller_Task
	 */
	protected $task;

	/**
	 * Входные данные
     *
	 * @var Data_Transport
	 */
	protected $input;
    
    /**
     * Будут ли использоваться внедрения зависимостей в данном контроллере
     * 
     * @var boolean
     */
    protected $hasInjections = false;

	/**
	 * Выходные данные
     *
	 * @var Data_Transport
	 */
	protected $output;

	/**
	 * Конфиг контроллера
     *
	 * @var array
	 */
	protected $config = array();

	/**
	 * Завершение работы контроллера ошибкой
     *
	 * @param string $text Текст ошибки. Не отображается пользователю,
	 * виден в консоли отладки.
	 * @param string $method Экшен, в котором произошла ошибка (__METHOD__)
	 * или шаблон (в этому случае метод будет взять из _currentAction).
	 * Необходим для определения шаблона. Если не передан, будет
	 * взято из $text.
	 * @param string $tpl [optional] Шаблон.
	 */
	protected function sendError($text, $method = null, $tpl = true)
	{
		$this->output->send(array(
			'error'	=> array(
				'text'	=> $text,
				'tpl'	=> $tpl
			),
		));
		if (!$method) {
			$method = $text;
		}
		if (!is_bool($tpl)) {
			$this->task->setClassTpl($method, $tpl);
		} elseif ($method) {
			if (strpos($method, '/') === false) {
				$this->task->setClassTpl(
                    $this->currentAction, '/' . ltrim($method, '/')
				);
			} else {
				$this->task->setClassTpl($method);
			}
		}
	}

	/**
	 * Загружает и возвращает конфиг для контроллера
     *
	 * @return Objective
	 */
	public function config()
	{
		if (is_array($this->config)) {
			$configManager = $this->getService('configManager');
            $this->config = $configManager->get(
                get_class($this), $this->config
			);
		}
		return $this->config;
	}

    /**
     * Получить текущий входной транспорт
     *
	 * @return Data_Transport
	 */
	public function getInput()
	{
		return $this->input;
	}

    /**
     * Получить текущий выходной транспорт
     *
	 * @return Data_Transport
	 */
	public function getOutput()
	{
		return $this->output;
	}

    /**
     * Получить услугу по имени
     *
     * @param string $serviceName
     * @return mixed
     */
    public function getService($serviceName)
    {
        if (!self::$serviceLocator) {
            self::$serviceLocator = new Service_Locator;
        }
        return self::$serviceLocator->getService($serviceName);
    }
    
    /**
     * Получить сервис локатор
     * 
     * @return Service_Locator
     */
    public function getServiceLocator()
    {
        return self::$serviceLocator;
    }

	/**
	 * Возвращает текущую задачу контролера
     *
	 * @return Controller_Task
	 */
	public function getTask()
	{
		return $this->task;
	}
    
    /**
     * Будут ли использоваться внедрения зависимостей в данном контроллере
     * 
     * @return boolean
     */
    public function hasInjections()
    {
        return $this->hasInjections;
    }

	/**
	 * Имя контроллера (без приставки Controller_)
     *
	 * @return string
	 */
	final public function name()
	{
		return substr(get_class($this), strlen('Controller_'));
	}

	/**
	 * Заменить текущий экшн с передачей всех параметров
     *
     * @param string $controller
     * @param string $action
	 */
	public function replaceAction($controller, $action = 'index')
	{
		if ($controller instanceof Controller_Abstract) {
			$other = $controller;
			$controller = $other->name();
		} else {
            $controllerManager = $this->getService('controllerManager');
			$other = $controllerManager->get($controller);
		}
		$this->task->setTemplate(
			'Controller/' . str_replace('_', '/', $controller) . '/' . $action
		);
		if ($controller == get_class($this)) {
			return $this->$action();
		} else {
			$other->setInput($this->input);
			$other->setOutput($this->output);
			$other->setTask($this->task);
            $other->$action();
		}
	}

	/**
	 * Заменить текущую задачу контроллера
     *
	 * @param Controller_Task $task
	 * @return Controller_Abstract
	 */
	public function setTask($task)
	{
		$this->task = $task;
		return $this;
	}

	/**
	 * Устанавливает транспорт входных данных
     *
	 * @param Data_Transport $input
	 * @return Controller_Abstract
	 */
	public function setInput($input)
	{
		$this->input = $input;
		return $this;
	}

    /**
     * Изменить факт инъекции в экшин
     * 
     * @param boolean $value
     */
    public function setHasInjections($value)
    {
        $this->hasInjections = $value;
    }
    
	/**
	 * Устанавливает транспорт выходных данных
     *
	 * @param Data_Transport $output
	 * @return Controller_Abstract
	 */
	public function setOutput($output)
	{
		$this->output = $output;
		return $this;
	}
    
    /**
     * Изменить локатор сервисов
     * 
     * @param Service_Locator $serviceLocator
     */
    public function setServiceLocator($serviceLocator)
    {
        self::$serviceLocator = $serviceLocator;
    }
}