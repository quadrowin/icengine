<?php
/**
 *
 * @desc Фронт контроллер.
 * @author Юрий Шведов, Илья Колесников
 * @package IcEngine
 *
 */
class Controller_Front extends Controller_Abstract
{
	
	/**
	 * @desc Конфиг
	 * @var array
	 */
	protected $_config = array(
		// Варианты фронт контроллера
		'options' => array(
			// Для консоли
			'Console' => array(
				// контроллер
				'controller' => 'Front_Cli',
				// входной транспорт (см. Data_Transport_Manager config)
				'input' => 'cli_input'
			),
			// Для остальных запросов
			'Always' => array(
				'controller' => 'Front_Router',
				'input' => 'default_input'
			)
		)
	);
	
	/**
	 * @desc Запускаем фронт контролер.
	 */
	public function index ()
	{
		$config = $this->config ();
		
		foreach ($config->options as $checker => $option)
		{
			$checker_class = 'Controller_Checker_' . $checker;
			Loader::load ($checker_class);
			if ($checker_class::check ())
			{
				$input = Data_Transport_Manager::get ($option->input);
				
				$task = Controller_Manager::call (
					$option->controller,
					'index',
					$input
				);
				
				$this->_output->send (array (
					'tasks' => $task->getTransaction ()->receive ('tasks')
				));
				
				return;
			}
		}
		
		throw new Exception ('No front controller.');
	}

}
