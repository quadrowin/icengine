<?php

/**
 * Менеджер отображений вида
 * 
 * @author goorus, morph
 * @Service("viewRenderManager")
 */
class View_Render_Manager extends Manager_Abstract
{
	/**
	 * Представления по имени.
	 * 
     * @var array <View_Render_Abstract>
	 */
	protected $views = array();

	/**
	 * Стэк представлений.
	 * 
     * @var array <View_Render_Abstract>
	 */
	protected $viewStack = array();

	/**
	 * Расширение шаблона по умолчанию
     * 
	 * @var string
	 */
	protected $templateExtension = '.tpl';

	/**
	 * @inheritdoc
	 */
	protected $config = array(
		/**
		 * @desc Рендер по умолчанию
		 * @var string
		 */
		'default_view'		=> 'Smarty'
	);

	/**
	 * Возвращает рендер по названию.
	 * 
     * @param string $name
	 * @return View_Render_Abstract
	 */
	public function byName($name)
	{
		if (isset($this->views[$name])) {
			return $this->views[$name];
		}
        $className = 'View_Render_' . $name;
        $view = new $className;
		$this->views[$name] = $view;
        return $view;
	}

	/**
	 * Выводит результат работы шаблонизатора в браузер.
	 */
	public function display($tpl)
	{
		$this->getView()->display($tpl);
	}

	/**
	 * Возвращает текущий рендер.
	 * 
     * @return View_Render_Abstract
	 */
	public function getView()
	{
		if (!$this->viewStack) {
			$config = $this->config();
			$this->pushViewByName($config['default_view']);
		}
		return end($this->viewStack);
	}

	/**
     * Получить расширение шаблона по умолчанию
     * 
	 * @return string
	 */
	public function getTemplateExtension()
	{
		return $this->templateExtension;
	}

	/**
     * Вытолкнуть вид из стэка
     * 
	 * @return View_Render_Abstract
	 */
	public function popView()
	{
		$view = array_pop($this->viewStack);
		$view->popVars();
		return $view;
	}

	/**
	 * Поместить вид в стэк
     * 
	 * @param View_Render_Abstract $view
	 * @return View_Render_Abstract
	 */
	public function pushView(View_Render_Abstract $view)
	{
		$this->viewStack[] = $view;
		$view->pushVars();
		return $view;
	}
    
	/**
	 * Получить вид по имени
	 *
	 * @param string $name
	 * @return View_Render_Abstract
	 */
	public function pushViewByName($name)
	{
		$view = $this->byName($name);
		return $this->pushView($view);
	}

	/**
	 * Изменить расширение шаблона по умолчанию
     * 
	 * @param string $value
	 */
	public function setTemplateExtension($value)
	{
		$this->templateExtension = $value;
	}

	/**
	 * Обработка шаблонов из стека.
	 * 
     * @param array $outputs
	 */
	public function render(array $outputs)
	{
		return $this->getView()->render($outputs);
	}
}