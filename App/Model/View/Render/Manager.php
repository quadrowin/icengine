<?php

namespace Ice;

/**
 *
 * @desc Менеджер рендеринга
 * @author Юрий Шведов, Илья Колесников
 * @package Ice
 *
 */
class View_Render_Manager extends Manager_Abstract
{

	/**
	 * @desc Представления по имени.
	 * @var array <View_Render_Abstract>
	 */
	protected $_views = array ();

	/**
	 * @desc Стэк представлений.
	 * @var array <View_Render_Abstract>
	 */
	protected $_viewStack = array ();

	/**
	 * @desc Конфиг
	 * @var array
	 */
	protected static $_config = array (
		/**
		 * @desc Рендер по умолчанию
		 * @var string
		 */
		'default_view' => 'Smarty'
	);

	/**
	 *
	 * @return Model_Manager
	 */
	protected function _getModelManager ()
	{
		return Core::di ()->getInstance ('Ice\\Model_Manager', $this);
	}

	/**
	 * @desc Возвращает рендер по названию.
	 * @param string $name
	 * @deprecated Следует использовать get.
	 * @return View_Render_Abstract
	 */
	public function byName ($name)
	{
		$class = Manager_Abstract::completeClassName ($name, 'View_Render');
		return $this->get ($class);
	}

	/**
	 * @desc Возвращает рендер по названию.
	 * @param string $class
	 * @return View_Render_Abstract
	 */
	public function get ($class)
	{
		if (isset ($this->_views [$class]))
		{
			return $this->_views [$class];
		}

		Loader::load ($class);

		$view = new $class (array ('id'	=> null));

		$this->_views [$class] = $view;

		return $view;
	}

	/**
	 * @desc Возвращает текущий рендер.
	 * @return View_Render_Abstract
	 */
	public function getDefaultView ()
	{
		$config = $this->config ();
		return $this->byName ($config ['default_view']);
	}

	/**
	 * @desc Возвращает текущий рендер.
	 * @return View_Render_Abstract
	 */
	public function getView ()
	{
		if (!$this->_viewStack)
		{
			Loader::load ('View_Render');

			$config = $this->config ();

			$this->pushViewByName ($config ['default_view']);
			//self::$_view = new View_Render (array('name' => self::$_defaultView));
		}

		return end ($this->_viewStack);
	}

	/**
	 * @return View_Render_Abstract
	 */
	public function popView ()
	{
		$view = array_pop ($this->_viewStack);
		$view->popVars ();
		return $view;
	}

	/**
	 *
	 * @param View_Render_Abstract $view
	 * @return View_Render_Abstract
	 */
	public function pushView (View_Render_Abstract $view)
	{
		$this->_viewStack [] = $view;
		$view->pushVars ();
		return $view;
	}

	/**
	 *
	 * @param integer $id
	 * @return View_Render_Abstract
	 */
	public function pushViewById ($id)
	{
		$view = $this->_getModelManager ()->byKey ('View_Render', $id);
		return $this->pushView ($view);
	}

	/**
	 *
	 *
	 * @param string $name
	 * @return View_Render_Abstract
	 */
	public function pushViewByName ($name)
	{
		$view = $this->byName ($name);
		return $this->pushView ($view);
	}

}
