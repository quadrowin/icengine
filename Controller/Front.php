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
	 * @desc Получаем (и инициализируем, если еще не проинициализирован) текущий диспетчер
	 * @return Controller_Dispatcher
	 */
	public function getDispatcher ()
	{
		Loader::load ('Controller_Dispatcher');
		return Controller_Dispatcher::instance ();
	}
	
	public function inputTransport ()
	{
		
	}
	
	/**
	 * @desc Запускаем фронт контролер.
	 */
	public function run ()
	{
		Loader::load ('Router');
		// Начинаем роутинг
		$route = Router::getRoute ();
		
		// Инициализируем вьюшник из запроса
		$view = View_Render_Manager::pushView ($route->viewRender ());
		
		// Отправляем сообщение, что вью был изменен
		Loader::load ('Message_After_Router_View_Set');
		Message_After_Router_View_Set::push ($route, $view);
		
		try 
		{
			// Получаем экшены
			$actions = $route->actions ();
			
			var_dump ($this->_input->getProviders());
			// Направляем входные данные в целевые контроллеры
			foreach ($actions as $action)
			{
				$action->set ('input', $this->_input);
			}
			
			// Выполнение экшенов
			$results = Controller_Manager::runTasks ($route->actions ());
			
			// Рендеринг
			View_Render_Manager::render ($results);
		}
		catch (Zend_Exception $e)
		{
			Loader::load ('Error');
			Error::render ($e);
		}
	}
	
}
