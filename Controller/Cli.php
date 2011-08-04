<?php
/**
 * 
 * @desc Контроллер консоли.
 * @author Юрий Шведов, Илья Колесников
 * @package IcEngine
 *
 */
class Controller_Cli extends Controller_Abstract 
{
	
	/**
	 * @desc Разобранные переменные из инпута
	 * @return Data_Transport
	 */
	protected function _parsedInput ()
	{
		Loader::load ('Data_Provider_Buffer');
		$buffer = new Data_Provider_Buffer ();
		$argv = $this->_input->receiveAll ();
		
		foreach ($argv as $arg)
		{
			$p = strpos ('=', $arg);
			if ($p)
			{
				$buffer->set (
					substr ($arg, 0, $p),
					substr ($arg, $p + 1)
				);
			}
		}
		
		$transport = new Data_Transport ();
		return $transport->appendProvider ($buffer);
	}
	
	/**
	 * @desc Запуск контроллера консоли
	 */
	public function index ()
	{
		Loader::load ('Controller_Dispatcher');
		
		try
		{
			$ca = explode ('/', $this->_input->receive (0));
			
			$action = Controller_Dispatcher::dispatch (
				$ca [0],
				isset ($ca [1]) ? $ca [1] : 'index'
			);
			
			Loader::load ('Controller_Action');
			$action = new Controller_Action (array (
				'controller'	=> $ca [0],
				'action'		=> $ca [1]
			));
			
			$task = new Controller_Task ($actions);
			$task->setInput ($this->_parsedInput ());
			
			/**
			 * @desc Выполненяем задания.
			 * @var array <Controller_Task>
			 */
			$tasks = Controller_Manager::runTasks (array ($task));
			
			$this->_output->send ('tasks', $tasks);
		}
		catch (Zend_Exception $e)
		{
			Loader::load ('Error');
			Error::render ($e);
		}
	}
	
}
