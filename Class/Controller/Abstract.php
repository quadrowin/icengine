<?php
/**
 * 
 * Базовый класс контроллера.
 * @author Юрий
 * @package IcEngine
 *
 */
class Controller_Abstract
{	
    
	/**
	 * Последний вызванный экшен.
	 * @var string
	 */
	protected $_currentAction;
	
	/**
	 * Текущая итерация диспетчера
	 * @var Controller_Dispatcher_Iteration
	 */
	protected $_dispatcherIteration;
	
	/**
	 * Входные данные
	 * @var Data_Transport
	 */
	protected $_input;
		
	/**
	 * Выходные данные.
	 * @var Data_Transport
	 */
	protected $_output;
	
	public function __construct ()
	{
	}
	
	/**
	 * Метод выполняется после вызова метода $action из диспетчера
	 * 
	 * @param string $action
	 * 		Вызываемый метод
	 */
	public function _afterAction ($action)
	{
		IcEngine::$application->messageQueue->push (
			'after::' . get_class ($this) . '::' . $action
		);
	}
	
	/**
	 * Метод выполняется перед вызовом метода $action из диспетчера
	 * 
	 * @param string $action
	 * 		Вызываемый метод
	 */
	public function _beforeAction ($action)
	{
		IcEngine::$application->messageQueue->push (
			'before::' . get_class ($this) . '::' . $action
		);
	}
	
	/**
	 * Результатом работы контроллера будет вызов метода хелпера.
	 * @param string $helper
	 * 		Название хелпера без перфикса "Helper_Action_"
	 * @param string $method
	 * 		Название метода хелпера.
	 */
	public function _helperReturn ($helper, $method)
	{
		$helper = 'Helper_Action_' . $helper;
		Loader::load ($helper);
		call_user_func (array ($helper, $method));
	}
	
	/**
	 * Временный контент для сохраняемых данных.
	 * @return Temp_Content|null
	 */
	public function _inputTempContent ()
	{
		Loader::load ('Temp_Content');
		$tc = Temp_Content::byUtcode ($this->_input->receive ('utcode'));
		
		if (!$tc)
		{
			Loader::load ('Helper_Action_Page');
			Helper_Action_Page::obsolete ();
			return;
		}
		
		return $tc;
	}
	
	/**
	 * Сохранение данных с формы
	 * @param Temp_Content $tc
	 * @param array $scheme
	 * @param string|Model $model_class [optional]
	 * 		Имя класса модели или модель.
	 * 		Если не задано, будет использвано имя контроллера.
	 * 		Пример: для контроллера <i>Controller_Sample</i>, результатом
	 * 		будет модель класса <i>Sample</i>.
	 * @return Model|null
	 * 		Сохраненная модель, либо null в случае ошибки.
	 */
	public function _savePostModel (Temp_Content $tc, $scheme, 
		$model_class = null)
	{
		Loader::load ('Helper_Form');
		$data = Helper_Form::receiveFields ($this->_input, $scheme);
		
		Helper_Form::filter ($data, $scheme);
		
		$valid = Helper_Form::validate ($data, $scheme);
		
		if (is_array ($valid))
		{
			$this->_dispatcherIteration->setTemplate (
				str_replace (array ('::', '_'), '/', reset ($valid)) . 
				'.tpl'
			);
			$this->_output->send (array (
						'field'	=> key ($valid),
						'field_title'=>isset($scheme['fio']['title']) ? $scheme['fio']['title'] : null,
						'data'	=> array (
							'field'	=> key ($valid),
							'error'	=> current ($valid)
						)
			));
			return null;
		}
		
		if ($model_class instanceof Model)
		{
			$model = $model_class;
		}
		else
		{
			if (!$model_class)
			{
				$model_class = $this->name ();
			}
			$model = IcEngine::$modelManager->get (
				$model_class,
				$tc->rowId
			);
		}
		
		$parts = Helper_Form::extractParts ($data, $scheme);

		$model->update ($parts ['fields']);
		
		
		if ($parts ['attributes'])
		{ 
			$model->attr ($parts ['attributes']);
		};
			
		return $model;
	}
	
	/**
	 * @return Data_Transport
	 */
	public function getInput ()
	{
		return $this->_input;
	}
	
	/**
	 * @return Data_Transport
	 */
	public function getOutput ()
	{
		return $this->_output;
	}
	
	public function index ()
	{
		
	}
	
	/**
	 * Имя контроллера (без приставки Controller_)
	 * 
	 * @return string
	 */
	public function name ()
	{		
		return substr (get_class ($this), 11);
	}
	
	/**
	 * Заменить текущий экшн с передачей всех параметров
	 */
	public function replaceAction ($controller, $action)
	{
		if ($controller instanceof Controller_Abstract)
		{
			$other = $controller;
			$controller = $other->name ();
		}
		else
		{
			$other = Controller_Broker::get ($controller);
		}
		
		$this->_dispatcherIteration->setTemplate (
			'Controller/' .
			str_replace ('_', '/', $controller) .
			'/' . $action . '.tpl'
		);
		
		if ($controller == get_class ($this))
		{
			// Этот же контроллер
			return $this->$action ();
		}
		else
		{
			$other = Controller_Broker::get ($controller);
			$other->setInput ($this->_input);
			$other->setOutput ($this->_output);
			$other->setDispatcherIteration ($this->_dispatcherIteration);
			return $other->$action ();
		}
	}
	
	/**
	 * 
	 * @param Controller_Dispatcher_Iteration $iteration
	 * @return Controller_Abstract
	 */
	public function setDispatcherIteration (
	    Controller_Dispatcher_Iteration $iteration)
	{
	    $this->_dispatcherIteration = $iteration;
	    return $this;
	}
	
	public function setInput (Data_Transport $input)
	{
		$this->_input = $input;
		return $this;
	}
	
	public function setOutput (Data_Transport $output)
	{
		$this->_output = $output;
		return $this;
	}

}