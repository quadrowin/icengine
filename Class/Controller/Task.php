<?php

/**
 * Задание на выполнение контроллер
 *
 * @author goorus, morph
 */
class Controller_Task
{
	/**
	 * Переменная, куда попадет результат работы контролера
     *
	 * @var string
	 */
	protected $assignVar = 'content';

	/**
	 * Действие контроллера
     *
	 * @var Controller_Action
	 */
	protected $controllerAction;

	/**
	 * Игнорировать текущее задание
	 *
     * @var boolean
	 */
	protected $ignore = false;

	/**
	 * Индекс текущего задания
     *
	 * @var integer
	 */
	protected $index;

	/**
	 * Входные данные
     *
	 * @var Data_Transport
	 */
	protected $input;

	/**
	 * Название шаблона
	 *
     * @var string
	 */
	protected $template;

	/**
	 * Результат выполнения действия контроллера
	 *
     * @var Data_Transport_Transaction
	 */
	protected $transaction;

	/**
	 * Рендера
	 *
     * @var View_Render_Abstract
	 */
	protected $viewRender;

	/**
	 * Конструктор
     *
	 * @param Controller_Action $action
	 */
	public function __construct($action)
	{
        if (!empty($action['assign'])) {
            $this->assignVar = $action['assign'];
        }
        $this->index = !empty($action['sort']) ? $action['sort'] : 0;
        $this->ignore = false;
        $serviceLocator = IcEngine::serviceLocator();
        $route = $serviceLocator->getService('router')->getRoute();
        if ($route->params && isset($route->params['View_Render__id'])) {
            $this->viewRender = $route->viewRender();
        } else {
            $viewRenderManager = $serviceLocator->getService(
                'viewRenderManager'
            );
            $this->viewRender = $viewRenderManager->getView();
        }
        $this->controllerAction = array(
            'controller'    => $action['controller'],
            'action'        => $action['action']
        );
        $this->template = $this->getTemplateName($action);
	}

	/**
	 * Получить экшин
     *
	 * @return Controller_Action
	 */
	public function controllerAction()
	{
		return $this->controllerAction;
	}

	/**
	 * Возвращает название переменной, в которую будет присвоено
	 * результат рендера.
     *
	 * @return string
	 */
	public function getAssignVar()
	{
		return $this->assignVar;
	}

	/**
	 * Узнать игнорируется ли текущая задача
     *
	 * @return boolean
	 */
	public function getIgnore()
	{
		return $this->ignore;
	}

	/**
	 * Получить порядковый номер задания в очереди заданий
     *
	 * @return integer
	 */
	public function getIndex()
	{
		return $this->index;
	}

	/**
	 * Получить транспорт входных данных
     *
	 * @return Data_Transport
	 */
	public function getInput()
	{
		return $this->input;
	}

	/**
	 * Возвращает имя шаблона
     *
	 * @return string
	 */
	public function getTemplate()
	{
		return $this->template;
	}

    /**
     * Сформировать имя шаблона для задания
     *
     * @param array $action
     * @return string
     */
    public function getTemplateName($action)
    {
        return 'Controller/' . str_replace('_', '/', $action['controller']) .
            '/' . $action['action'];
    }

	/**
	 * Получить транзакцию экшина
     *
	 * @return Data_Transport_Transaction
	 */
	public function getTransaction()
	{
		return $this->transaction;
	}

	/**
	 * Получить рендер
     *
	 * @return View_Render_Abstract
	 */
	public function getViewRender()
	{
		return $this->viewRender;
	}

	/**
     * Задать шаблон на основе названия класса
     *
	 * @param string $class Класс или метод (контроллера).
	 * @param string $template Шаблон.
	 */
	public function setClassTpl($class, $template = '')
	{
		$template = $template ? ('/' . ltrim($template, '/')) : '';
		$this->setTemplate(
			str_replace(array('_', '::'), '/', $class) . $template
		);
	}

	/**
	 * Установить флаг игнорирования текущего задания
     *
	 * @param boolean $value
	 */
	public function setIgnore($value)
	{
		$this->ignore = (bool) $value;
	}

	/**
	 * Установить порядковый номер заания в очереди заданий
	 * @param integer $value
	 */
	public function setIndex($value)
	{
		$this->index = $value;
	}

	/**
	 * Установить транспорт для входных данных
     *
	 * @param Data_Transport $input
	 */
	public function setInput(Data_Transport $input)
	{
		$this->input = $input;
	}

	/**
	 * Установка шаблона для рендера
     *
	 * @param string $value
	 */
	public function setTemplate($value)
	{
		$this->template = $value;
	}

	/**
	 * Изменить транзакцию текущего экшина
     *
	 * @param Data_Transport_Transaction $value
	 */
	public function setTransaction(Data_Transport_Transaction $value)
	{
		$this->transaction = $value;
	}

	/**
	 * Изменить рендер
     *
	 * @param View_Render_Abstract $viewRender
	 */
	public function setViewRender(View_Render_Abstract $viewRender)
	{
		$this->viewRender = $viewRender;
    }
}