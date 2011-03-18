<?php
/**
 * 
 * @desc Базовый класс контроллера.
 * @author Юрий
 * @package IcEngine
 *
 */
class Controller_Abstract
{	
	
	/**
	 * @desc Последний вызванный экшен.
	 * @var string
	 */
	protected $_currentAction;
	
	/**
	 * @desc Текущая итерация диспетчера
	 * @var Controller_Dispatcher_Iteration
	 */
	protected $_dispatcherIteration;
	
	/**
	 * @desc Входные данные
	 * @var Data_Transport
	 */
	protected $_input;
		
	/**
	 * @desc Выходные данные.
	 * @var Data_Transport
	 */
	protected $_output;
	
	/**
	 * @desc Конфиг контроллера
	 * @var array
	 */
	public $config = array ();
	
	public function __construct ()
	{
	}
	
	/**
	 * @desc Метод выполняется после вызова метода $action из диспетчера
	 * @param string $action Вызываемый метод.
	 */
	public function _afterAction ($action)
	{
		IcEngine::$application->messageQueue->push (
			'after::' . get_class ($this) . '::' . $action
		);
	}
	
	/**
	 * @desc Метод выполняется перед вызовом метода $action из диспетчера
	 * @param string $action Вызываемый метод.
	 */
	public function _beforeAction ($action)
	{
		IcEngine::$application->messageQueue->push (
			'before::' . get_class ($this) . '::' . $action
		);
	}
	
	/**
	 * @desc Результатом работы контроллера будет вызов метода хелпера.
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
	 * @desc Временный контент для сохраняемых данных.
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
	 * @desc Получение данных с формы
	 * @param array|Objective $scheme
	 * @param boolean|Temp_Content Использовать ли временный контент или 
	 * 		сам временный котенты
	 * @param boolean $by_parts Вернуть разбитым по частям (данные и атрибуты)
	 * @return Objective
	 */
	public function _inputFormData ($scheme, $use_tc = null, $by_parts = true)
	{
		Loader::load ('Helper_Form');
		
		// временный контент
		if ($use_tc)
		{
			Loader::load ('Temp_Content');
			if (!($use_tc instanceof Temp_Content))
			{
				$use_tc = Temp_Content::byUtcode (
					$this->_input->receive ('utcode')
				);
				
				if (!$use_tc)
				{
					$this->_helperReturn ('Page', 'obsolete');
					return false;
				}
			}
		}
		
		$data = Helper_Form::receiveFields (
			$this->_input,
			$scheme,
			$use_tc
		);
		
		Helper_Form::filter ($data, $scheme);
		
		$valid = Helper_Form::validate ($data, $scheme);
		if (is_array ($valid))
		{
			// ошибка валидации
			$this->_dispatcherIteration->setClassTpl (reset ($valid));
			
			$this->_output->send (array (
				'registration'	=> $valid,
				'data'			=> array (
					'field'			=> key ($valid),
					'error'			=> current ($valid)
				)
			));
			
			return false;
		}
		
		Helper_Form::unsetIngored ($data, $scheme);
		
		return Helper_Form::extractParts ($data, $scheme);
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
			$field = key ($valid);
			$this->_output->send (array (
				'field'		=> $field,
				'field_title'	=>isset ($scheme [$field]['title']) ? $scheme [$field]['title'] : null,
				'data'		=> array (
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
	 * @desc Загружает и возвращает конфиг для контроллера
	 * @return Objective
	 */
	public function config ()
	{
		if (is_array ($this->config))
		{
			$this->config = Config_Manager::get (
				get_class ($this),
				$this->config
			);
		}
		return $this->config;
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
	
	/**
	 * @desc Экшн по умолчанию
	 */
	public function index ()
	{
		
	}
	
	/**
	 * @desc Имя контроллера (без приставки Controller_)
	 * @return string
	 */
	public function name ()
	{		
		return substr (get_class ($this), 11);
	}
	
	/**
	 * @desc Заменить текущий экшн с передачей всех параметров
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