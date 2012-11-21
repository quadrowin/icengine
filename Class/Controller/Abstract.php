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
	 * Текущая задача
     *
	 * @var Controller_Task
	 */
	protected $_task;

	/**
	 * Входные данные
     *
	 * @var Data_Transport
	 */
	protected $_input;

	/**
	 * Выходные данные
     *
	 * @var Data_Transport
	 */
	protected $_output;

	/**
	 * Конфиг контроллера
     *
	 * @var array
	 */
	protected $_config = array();

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
	protected function _sendError($text, $method = null, $tpl = true)
	{
		$this->_output->send(array(
			'error'	=> array(
				'text'	=> $text,
				'tpl'	=> $tpl
			),
		));
		if (!$method) {
			$method = $text;
		}
		if (!is_bool($tpl)) {
			$this->_task->setClassTpl($method, $tpl);
		} elseif ($method) {
			if (strpos($method, '/') === false) {
				$this->_task->setClassTpl(
                    $this->currentAction, '/' . ltrim($method, '/')
				);
			} else {
				$this->_task->setClassTpl($method);
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
		if (is_array($this->_config)) {
			$this->_config = Config_Manager::get(
                get_class($this), $this->_config
			);
		}
		return $this->_config;
	}

	/**
	 * Возвращает текущую задачу контролера
     *
	 * @return Controller_Task
	 */
	public function getTask()
	{
		return $this->_task;
	}

	/**
     * Получить текущий входной транспорт
     *
	 * @return Data_Transport
	 */
	public function getInput()
	{
		return $this->_input;
	}

	/**
     * Получить текущий выходной транспорт
     *
	 * @return Data_Transport
	 */
	public function getOutput()
	{
		return $this->_output;
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
			$other = Controller_Manager::get($controller);
		}
		$this->_task->setTemplate(
			'Controller/' . str_replace('_', '/', $controller) . '/' . $action
		);
		if ($controller == get_class($this)) {
			return $this->$action();
		} else {
			$other->setInput($this->_input);
			$other->setOutput($this->_output);
			$other->setTask($this->_task);
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
		$this->_task = $task;
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
		$this->_input = $input;
		return $this;
	}

	/**
	 * Устанавливает транспорт выходных данных
     *
	 * @param Data_Transport $output
	 * @return Controller_Abstract
	 */
	public function setOutput($output)
	{
		$this->_output = $output;
		return $this;
	}
}