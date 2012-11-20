<?php

/**
 * Менеджер контроллеров
 *
 * @author goorus, morph
 */
class Controller_Manager extends Manager_Abstract
{
    /**
     * Акшин по умолчанию
     */
    const DEFAULT_ACTION = 'index';

    /**
     * Имя рендера по умолчанию
     */
    const DEFAULT_VIEW = 'Smarty';

	/**
	 * Текущее задание
     *
	 * @var Controller_Task
	 */
	protected static $currentTask;

	/**
	 * Транспорт входных данных
     *
	 * @var Data_Transport
	 */
	protected static $input;

    /**
     * Сообщение последней ошибки
     *
     * @var string
     */
    protected static $lastError;

	/**
	 * ранспорт выходных данных
     *
	 * @var Data_Transport
	 */
	protected static $output;

	/**
	 * Отложенные очереди заданий
     *
	 * @var array <array>
	 */
	protected static $tasksBuffer = array ();

	/**
	 * Очередь заданий
     *
	 * @var array <Router_Action>
	 */
	protected static $tasksQueue = array ();

	/**
	 * Результаты выполнения очереди
     *
	 * @var array <Controller_Task>
	 */
	protected static $tasksResults = array ();

	/**
	 * Буффер результатов
     *
	 * @var array <array <Controller_Task>>
	 */
	protected static $tasksResultsBuffer = array ();

	/**
	 * @inheritdoc
	 */
	protected static $_config = array (
		/**
		 * @desc Фильтры для выходных данных
		 * @var array
		 */
		'output_filters'	=> array (),
		/**
		 * @desc Настройки кэширования для экшенов.
		 * @var array
		 */
		'actions'			=> array ()
	);

    /**
     * Добавляет выходные фильтры по умолчанию
     *
     * @param Data_Transport $output
     */
    public static function appendOutputFilters($output)
    {
        $filters = self::config ()->output_filters;
        if (!$filters) {
            return;
        }
        foreach ($filters as $filter) {
            $filterClass = 'Filter_' . $filter;
            $filter = new $filterClass;
            $output->outputFilters()->append($filter);
        }
    }

	/**
	 * Вызов экшена контроллера
     *
	 * @param string $controllerName Название контроллера.
	 * @param string $actionName Метод.
	 * @param array|Data_Transport $input Входные данные.
	 * @param Controller_Task $task [optional] Задание
     * @param boolean $notLogging [optional] не логировать ли контроллер
	 * @return Controller_Task
	 */
	public static function call($controllerName, $actionName, $input = array(),
		$task = null, $notLogging = false)
	{
		if (Tracer::$enabled && !$notLogging) {
			Tracer::resetDeltaModelCount();
			Tracer::resetDeltaQueryCount();
			Tracer::begin(
                __CLASS__, __METHOD__, __LINE__, $controllerName, $actionName
            );
		}
		if (!$task) {
			$task = self::createEmptyTask($controllerName, $actionName);
		}
		$controller = self::get($controllerName);
		$lastInput = $controller->getInput();
		$lastOutput = $controller->getOutput();
		$lastTask = $controller->getTask();
		if (is_null($input)) {
			$input = self::getInput();
		} elseif (is_array($input)) {
            $input = self::createTransport($input);
		}
        $output = self::getOutput();
        $controller
            ->setInput($input)
            ->setOutput($output)
            ->setTask($task);
		$output->beginTransaction();
        $params = self::sendToTransportFromActionArgs($controller, $actionName);
        call_user_func_array(array($controller, $actionName), (array) $params);
		$task->setTransaction($output->endTransaction());
		$controller
			->setInput($lastInput)
			->setOutput($lastOutput)
			->setTask($lastTask);
		if (Tracer::$enabled && !$notLogging) {
			$deltaModelCount = Tracer::getDeltaModelCount();
			$deltaQueryCount = Tracer::getDeltaQueryCount();
			Tracer::incControllerCount();
			Tracer::end($deltaModelCount, $deltaQueryCount,
				memory_get_usage(), 0);
		}
		return $task;
	}

    /**
     * Создает опции вызова контроллера по умолчанию
     *
     * @param array $options
     * @return array
     */
    public static function createEmptyOptions($options)
    {
        $options = array('full_result' => !$options);
        return $options;
    }

    /**
     * Создает новое пустое задание
     *
     * @param string $controller
     * @param string $action
     * @return \Controller_Task
     */
    public static function createEmptyTask($controller, $action)
    {
        $task = new Controller_Task(array(
            'id'			=> null,
            'controller'	=> $controller,
            'action'		=> $action,
            'assign'        => '',
            'sort'          => 0
        ));
        return $task;
    }

    /**
     * Создает новый экшин из данных массива
     *
     * @param array $action
     * @return \Controller_Action
     */
    public static function createFromArray($action)
    {
        return new Controller_Action (array (
            'controller'	=> $action['controller'],
            'action'		=> $action['action']
        ));
    }

    /**
     * Создает результат работы контроллера по умолчанию
     *
     * @param array $buffer
     * @return array
     */
    public static function createResult($buffer)
    {
        return array (
			'error'		=> isset($buffer['error']) ? $buffer['error'] : '',
			'data'		=>
				isset($buffer['data']) ? $buffer['data'] : array(),
			'html'		=> null
		);
    }

	/**
	 * Создаем задания из экшинов
     *
	 * @param array $actions
	 * @param Data_Transport $input
	 * @return array
	 */
	public static function createTasks($actions, Data_Transport $input)
	{
		$tasks = array();
		foreach ($actions as $action) {
			$task = new Controller_Task($action);
			$task->setInput($input);
			$tasks[] = $task;
		}
		return $tasks;
	}

    /**
     * Создает новый транспорт и начинает транзакцию для него
     *
     * @param array $input
     * @return \Data_Transport
     */
    public static function createTransport($input)
    {
        $transport = new Data_Transport();
        $transport->beginTransaction ()->send($input);
        return $transport;
    }

	/**
	 * Очистка результатов работы контроллеров
	 */
	public static function flushResults()
	{
		self::$tasksResults = array();
	}

	/**
	 * Возвращает контроллер по названию
     *
	 * @param string $controller_name
	 * @return Controller_Abstract
	 */
	public static function get($controllerName)
	{
        $className = 'Controller_' . $controllerName;
        if (!class_exists($className)) {
            throw new Exception ("Controller $controllerName not found.");
        }
        $controller = new $className;
        return $controller;
	}

    /**
	 * Настройки кэширования для контроллера-экшена.
	 *
     * @param string $controller Контроллер
	 * @param string $action Экшен
	 * @return Objective
	 */
	protected static function getCacheConfig($controller, $action)
	{
		$selfConfig = self::config();
        $controllerAction = $controller . '::' . $action;
        $controllerConfig = $selfConfig->actions[$controllerAction];
        if (!$controllerConfig) {
            if (!$selfConfig->actions[$controller]) {
                return;
            }
            $controllerConfig = $selfConfig->actions[$controller];
        }
        if ($controllerConfig->cache_config) {
            return call_user_func(
                $controllerConfig->cache_config, $controllerConfig
            );
        }
        if ($controllerConfig->tags && $controllerConfig->tag_provider) {
            $provider = Data_Provider_Manager::get(
                $controllerConfig->tag_provider
            );
            $tagNames = $controllerConfig->tags->__toArray();
            $tags = $provider->getTags($tagNames);
            $controllerConfig->current_tags = $tags;
        }
        return $controllerConfig;
	}

	/**
     * Получить входной транспорт по умолчанию
     *
	 * @return Data_Transport
	 */
	public static function getInput()
	{
		if (!self::$input) {
			self::$input  = new Data_Transport();
		}
		return self::$input;
	}

	/**
	 * Возвращает транспорт для выходных данных по умолчанию.
	 *
     * @return Data_Transport
	 */
	public static function getOutput ()
	{
		if (!self::$output) {
			self::$output = new Data_Transport();
            self::appendOutputFilters(self::$output);
		}
		return self::$output;
	}

	/**
	 * Выполняет указанный контроллер, экшен с заданными параметрами
     *
	 * @param string $controllerAction Название контроллера или контроллер и
     * экшен в формате "Controller/action".
	 * @param array $args Параметры контроллера.
	 * @param mixed $options=true Параметры вызова.
	 * @return string Результат компиляции шабона.
	 * @todo Это будет в Controller_Render
	 * @tutorial
	 * 		html ('Controller', array ('param'	=> 'val'));
	 * 		html ('Controller/action')
	 */
	public static function html($controllerAction, array $args = array(),
		$options = true)
	{
        $controllerAction = explode('/', $controllerAction);
		if (!isset($controllerAction[1])) {
            $controllerAction[1] = self::DEFAULT_ACTION;
        }
        $cacheConfig = self::getCacheConfig(
            $controllerAction[0], $controllerAction[1]
        );
        if (is_bool($options)) {
            $options = self::createEmptyOptions($options);
        }
		$html = Executor::execute(
			array(__CLASS__, 'htmlUncached'),
			array($controllerAction, $args, $options),
			$cacheConfig
		);
		return $html;
	}

	/**
	 * Выполняет указанный контроллер, экшен с заданными параметрами,
	 * не используется кэширование
     *
	 * @param string $action Название контроллера или контроллер и экшен
	 * в формате "Controller/action".
	 * @param array $args Параметры контроллера.
	 * @param mixed $options=true Параметры вызова.
	 * @return string Результат компиляции шабона.
	 * @todo Это будет в Controller_Render
	 * @tutorial
	 * 		html ('Controller', array ('param'	=> 'val'));
	 * 		html ('Controller/action')
	 */
	public static function htmlUncached($controllerAction, array $args=array (),
		$options = true)
	{
		$controllerAction = is_array($controllerAction)
            ? $controllerAction : explode ('/', $controllerAction);
        if (!isset($controllerAction[1])) {
            $controllerAction[1] = self::DEFAULT_ACTION;
        }
		if (Tracer::$enabled) {
			Tracer::resetDeltaModelCount();
			Tracer::resetDeltaQueryCount();
			Tracer::begin(
                __CLASS__, __METHOD__, __LINE__,
                $controllerAction[0], $controllerAction[1]
            );
		}
        $task = self::call(
            $controllerAction[0], $controllerAction[1], $args, null, true
        );
        self::$lastError = null;
		$buffer = $task->getTransaction()->buffer();
		$result = self::createResult($buffer);
        $template = $task->getTemplate();
        if (Tracer::$enabled) {
            $startTime = microtime(true);
        }
        if ($template) {
            $result['html'] = self::renderTemplate($buffer, $template);
        }
        if (self::$lastError) {
            $result['error'] = self::$lastError;
        }
		if (Tracer::$enabled) {
            $endTime = microtime(true);
			$deltaModelCount = Tracer::getDeltaModelCount();
			$deltaQueryCount = Tracer::getDeltaQueryCount();
			$delta = $endTime - $startTime;
			Tracer::incRenderTime($delta);
			Tracer::incControllerCount();
			Tracer::end($deltaModelCount, $deltaQueryCount, memory_get_usage(),
				$delta);
		}
		if (!empty($options['with_buffer'])) {
			$options = array('full_result' => true);
			$result['buffer'] = $buffer;
		}
        elseif (is_bool($options)) {
            $options = self::createEmptyOptions($options);
        }
		return !empty($options['full_result']) ? $result : $result['html'];
	}

    /**
     * Логирует ошибку
     *
     * @param Exception $e
     */
    public static function logError($e)
    {
         $msg = '[' . $e->getFile() . '@' .
            $e->getLine() . ':' .
            $e->getCode() . '] ' .
            $e->getMessage() . PHP_EOL;
        error_log(
            $msg . PHP_EOL .
            $e->getTraceAsString() . PHP_EOL,
            E_USER_ERROR, 3
        );
        Debug::log($msg);
    }

	/**
	 * Добавление задания в текущую очередь выполнения
     *
	 * @param mixed $action
	 */
	public static function pushTasks($action)
	{
        $actions = array();
        if (!is_array($action)) {
            $actions = array($action);
        } else {
            if (!isset($action[0])) {
                $action = self::createFromArray($action);
            }
            $actions = $action;
        }
        foreach ($actions as $action) {
            if (!($action instanceof Controller_Action)) {
                throw new Exception('Illegal action type');
            }
            self::$taskQueue[] = new Controller_Task($action);
        }
	}

    /**
     * Рендерит шаблон
     *
     * @param array $buffer
     * @param string $template
     */
    public static function renderTemplate($buffer, $template)
    {
        $view = View_Render_Manager::pushViewByName(self::DEFAULT_VIEW);
        try {
            $view->assign($buffer);
            $html = $view->fetch($template);
        } catch (Exception $e) {
            self::$lastError = 'Controller_Manager: Error in template.';
            self::logError($e);
        }
        View_Render_Manager::popView();
        return !empty($html) ? $html : null;
    }

	/**
	 * Запустить задание на выполнение
     *
	 * @param Controller_Task $task
	 * @return Controller_Task
	 */
	public static function run($task)
	{
		$parentTask = self::$currentTask;
		self::$currentTask = $task;
		$action = $task->controllerAction();
		$task = self::call(
			$action['controller'],
			$action['action'],
			$task->getInput(),
			$task
		);
		self::$currentTask = $parentTask;
		return $task;
	}

	/**
	 * Выполнение очереди заданий
     *
	 * @param array $tasks
	 * @return array
	 */
	public static function runTasks($tasks)
	{
		self::$tasksBuffer[] = self::$tasksQueue;
		self::$tasksResultsBuffer [] = self::$tasksResults;
		self::$tasksQueue = $tasks;
		self::$tasksResults = array();
        $taskCount = count(self::$tasksQueue);
		for ($i = 0; $i < $taskCount; ++$i) {
			$task = self::run(self::$tasksQueue[$i]);
			if (!$task->getIgnore()) {
				self::$tasksResults[] = $task;
			}
		}
		$result = self::$tasksResults;
		self::$tasksQueue = array_pop(self::$tasksBuffer);
		self::$tasksResults = array_pop(self::$tasksResultsBuffer);
		return $result;
	}

    /**
     * Отправить в входной транспорт аргументы, полученные рефлексией
     * из заголовка метода
     *
     * @param Controller_Abstract $controller
     * @param string $actionName
     * @return array
     */
    public static function sendToTransportFromActionArgs($controller,
        $actionName)
    {
        $reflection = new ReflectionMethod($controller, $actionName);
		$params = $reflection->getParameters();
        $currentInput = $controller->getInput();
        $provider = $currentInput->getProvider(0);
        $resultParams = array();
        if (!$params) {
            return array();
        }
        foreach ($params as $param) {
            $value = $currentInput->receive($param->name);
            if (!$value && $param->isOptional()) {
                $value = $param->getDefaultValue();
            }
            if ($provider) {
                $provider->set($param->name, $value);
            }
            $resultParams[$param->name] = $value;
        }
        return $resultParams;
    }
}