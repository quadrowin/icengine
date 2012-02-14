<?php

namespace Ice;

/**
 *
 * @desc Абстрактный класс рендера.
 * @author Yury Shvedov
 * @package Ice
 *
 */
abstract class View_Render_Abstract
{

	/**
	 * @desc Config
	 * @var array
	 */
	protected static $_config = array ();

	/**
	 * @desc Пути к директориям шаблонов.
	 * @var array <string>
	 */
	protected $_templatesPathes = array ();


	/**
	 * @desc Переменные шаблонизатора.
	 * @var array
	 */
	protected $_vars = array ();

	/**
	 * @desc Стек переменных.
	 * @var array
	 */
	protected $_varsStack = array ();

	/**
	 * @desc Добавление пути до директории с шаблонами
	 * @param string $path Директория с шаблонами.
	 */
	public function addTemplatesPath ($path)
	{
		$dir = rtrim ($path, '/');
		$this->_templatesPathes [] = $dir . '/';
	}

	/**
	 * @desc Устанавливает значение переменной в шаблоне
	 * @param string|array $key Имя переменной или массив
	 * пар (переменная => значение).
	 * @param mixed $value [optional] Новое значение переменной.
	 */
	public function assign ($key, $value = null)
	{
		if (is_array ($key))
		{
			$this->_vars = array_merge ($this->_vars, $key);
		}
		else
		{
			$this->_vars [$key] = $value;
		}
	}

	/**
	 * @desc Выводит результат работы шаблонизатор в браузер.
	 * @param string $tpl
	 */
	abstract public function display ($tpl);

	/**
	 * @desc Config
	 * @return Objective
	 */
	public static function config ()
	{
		if (is_array (static::$_config)) {
			$config_manager = Core::di ()->getInstance (
				'Ice\\Config_Manager',
				__CLASS__
			);
			static::$_config = $config_manager->get (
				get_called_class (),
				static::$_config
			);
		}
		return static::$_config;
	}

	/**
	 * @desc Обрабатывает шаблон и возвращает результат.
	 * @param string $tpl Шаблон
	 * @return mixed Результат работы шаблонизатора.
	 */
	abstract public function fetch ($tpl);

	/**
	 * @desc Возвращает массив путей до шаблонов.
	 * @return array
	 */
	public function getTemplatesPathes ()
	{
		return $this->_templatesPathes;
	}

	/**
	 * @desc Возвращает значение переменной шаблонизатора.
	 * @param string $key Имя переменной.
	 * @return mixed Значение переменной.
	 */
	public function getVar ($key)
	{
		return $this->_vars [$key];
	}

	/**
	 * @desc Название рендера
	 * @return string
	 */
	public function name ()
	{
		$class = get_class ($this);
		$p = strpos ($class, '\\');
		return substr ($class, 0, $p + 1) .
			substr ($class, $p + strlen ('\\View_Render_'));
	}

	/**
	 * @desc Восстанавливает значения переменных шаблонизатора
	 */
	public function popVars ()
	{
		$this->_vars = array_pop ($this->_varsStack);
	}

	/**
	 * @desc Сохраняет текущие значения переменных шаблонизатора и очищает их.
	 */
	public function pushVars ()
	{
		$this->_varsStack [] = $this->_vars;
		$this->_vars = array ();
	}

	/**
	 * @desc Обработка шаблонов из стека.
	 * @param Task $task
	 * @return mixed
	 */
	public function render (Task $task)
	{
		Loader::load ('Message_Before_Render');
		Message_Before_Render::push ($this);

		$this->assign ($task->getRequest ()->getInput ()->receiveAll ());

		$template = $task->getRequest ()->getExtra ('template');

		$result = $this->fetch ($template);

		$task->getResponse()->getOutput()->send ('content', $result);

		Loader::load ('Message_After_Render');
		Message_After_Render::push ($this);
	}

	/**
	 * (non-PHPdoc)
	 * @see Model_Factory_Delegate::table()
	 */
	public function table ()
	{
		return 'View_Render';
	}

}