<?php

/**
 * Абстрактный класс рендера.
 * 
 * @author goorus, morph
 */
abstract class View_Render_Abstract extends Model_Factory_Delegate
{
	/**
	 * Пути к директориям шаблонов.
	 * 
     * @var array <string>
	 */
	protected $templatesPathes = array();


	/**
	 * Переменные шаблонизатора.
	 * 
     * @var array
	 */
	protected $vars = array();

	/**
	 * Стек переменных.
	 * 
     * @var array
	 */
	protected $varsStack = array();

	/**
	 * (non-PHPdoc)
	 * @see Model::_afterConstruct()
	 */
	protected function _afterConstruct ()
	{
		if (!isset($this->fields['id'])) {
			$this->fields['id'] = 1;
		}
	}

	/**
	 * Добавление хелпера
	 * 
     * @param mixed $helper
	 * @param string $method
	 */
	public function addHelper($helper, $method)
	{

	}

	/**
	 * Добавление пути до директории с шаблонами
	 * 
     * @param string $path Директория с шаблонами.
	 */
	public function addTemplatesPath($path)
	{
		$dir = rtrim($path, '/');
		$this->templatesPathes[] = $dir . '/';
	}

	/**
	 * Устанавливает значение переменной в шаблоне
	 * 
     * @param string|array $key Имя переменной или массив
	 * пар (переменная => значение).
	 * @param mixed $value [optional] Новое значение переменной.
	 */
	public function assign($key, $value = null)
	{
		if (is_array($key)) {
			$this->vars = array_merge($this->vars, $key);
		} elseif (empty($key)) {
			throw new Exception('Empty key field.');
		} else {
			$this->vars[$key] = $value;
		}
	}

	/**
	 * Выводит результат работы шаблонизатор в браузер.
	 * 
     * @param string $tpl
	 */
	abstract public function display($tpl);

	/**
	 * Обрабатывает шаблон и возвращает результат.
	 * 
     * @param string $tpl Шаблон
	 * @return mixed Результат работы шаблонизатора.
	 */
	abstract public function fetch($tpl);

	/**
	 * Возвращает массив путей до шаблонов
     * 
	 * @return array
	 */
	public function getTemplatesPathes()
	{
		return $this->templatesPathes;
	}

	/**
	 * Возвращает значение переменной шаблонизатора.
	 * 
     * @param string $key Имя переменной.
	 * @return mixed Значение переменной.
	 */
	public function getVar($key)
	{
		return $this->vars[$key];
	}

	/**
	 * Восстанавливает значения переменных шаблонизатора
	 */
	public function popVars()
	{
		$this->vars = array_pop($this->varsStack);
	}

	/**
	 * Сохраняет текущие значения переменных шаблонизатора и очищает их.
	 */
	public function pushVars ()
	{
		$this->varsStack[] = $this->vars;
		$this->vars = array();
	}

	/**
	 * Обработка шаблонов из стека.
	 * 
     * @param array $outputs
	 * @return mixed
	 */
	public function render(Controller_Task $task)
	{
		$transaction = $task->getTransaction();
		$this->assign($transaction->buffer());
		$template = $task->getTemplate();
		$result = $template ? $this->fetch($template) : null;
		return $result;
	}

	/**
	 * (non-PHPdoc)
	 * @see Model_Factory_Delegate::table()
	 */
	public function table()
	{
		return 'View_Render';
	}
}