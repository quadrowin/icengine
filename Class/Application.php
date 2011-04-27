<?php

class Application
{
	
	/**
	 * Окружение
	 * 
	 * @var Application_Behavior_Abstract
	 */
	public $behavior;
	
	/**
	 * Фронт контроллер
	 * 
	 * @var Controller_Front
	 */
	public $frontController;
	
	/**
	 * Менеджер ресурсов
	 * 
	 * @var Resource_Manager
	 */
	public $resourceManager;
	
	/**
	 * Очередь сообщений
	 * 
	 * @var Message_Queue
	 */
	public $messageQueue;
	
	/**
	 * Подмена окружения
	 * @param string $behavior
	 */
	public function changeBehavior ($behavior, $path = '')
	{
		if (!$path)
		{
			if (class_exists ('Ice_Implementator'))
			{
				$path = Ice_Implementator::getClassesPath () . "Application/Behavior/$behavior.php";
			}
			else
			{
				$path = Ice_Implementation::getClassesPath () . "Application/Behavior/$behavior.php";
			}
		}
		
		$behavior = 'Application_Behavior_' . $behavior;
		
		Loader::load ('Application_Behavior_Abstract');
		if (class_exists ('Application_Bootstrap_IcePage'))
		{
			debug_print_backtrace ();
			die ();
		}
		
		include $path;
		
		$this->behavior = new $behavior ();
	}
	
	/**
	 * 
	 * @return Controller_Front
	 */
	public function frontController ()
	{
		if (!$this->frontController)
		{
			$this->frontController = new Controller_Front ();
		}
		return $this->frontController;
	}
	
	/**
	 * Инициализация окружения.
	 * @param string $behavior
	 * 		Окружение
	 * @param string $path
	 * 		Путь до файла окружения
	 */
	public function init ($behavior = '', $path = '')
	{
		if (!$behavior)
		{
			Loader::load ('Application_Bootstrap_Abstract');
			$this->behavior = new Application_Behavior_Abstract ();
		}
		else
		{
			$this->changeBehavior ($behavior, $path);
		}
	}
	
	/**
	 * Запуск рабочего фронт контроллера цикла.
	 */
	public function run ()
	{
		if (!$this->behavior->bootstrap)
		{
			$this->behavior->run ();
		}
		$this->frontController ()->run ();
	}
	
	/**
	 * Завершение работы.
	 * Вывод результата в бразуер.
	 */
	public function shutdown ()
	{
		Resource_Manager::save ();
		View_Render_Broker::display ();
	}
	
}