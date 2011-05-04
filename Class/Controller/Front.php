<?php
/**
 * 
 * @desc Фронт контроллер.
 * @author Юрий Шведов, Илья Колесников
 * @package IcEngine
 *
 */
class Controller_Front
{
	
	/**
	 * @desc Текущий диспетчер
	 * @var Controller_Dispatcher
	 */
	private $_dispatcher;
	
	/**
	 * @desc Получаем (и инициализируем, если еще не проинициализирован) текущий диспетчер
	 * @return Controller_Dispatcher
	 */
	public function getDispatcher ()
	{
		if (!$this->_dispatcher)
		{
			Loader::load ('Controller_Dispatcher');
			$this->_dispatcher = new Controller_Dispatcher;
		}
		return $this->_dispatcher;
	}
	
	/**
	 * @desc Запускаем фронт контролер.
	 */
	public function run ()
	{
		Loader::load ('Router');
		$route = Router::getRoute ();
		
		// Инициализируем вьюшник из запроса
		$view = View_Render_Broker::pushView ($route->viewRender ());
		
		// Отправляем сообщение, что вью был изменен
		Loader::load ('Message_After_Router_View_Set');
		Message_After_Router_View_Set::push ($route, $view);
		
		// Получаем диспетчер
//		Loader::load ('Controller_Dispatcher');
//		$this->_dispatcher = new Controller_Dispatcher;
		
		try 
		{
			View_Render_Broker::render (
				$this->getDispatcher ()
					// Закидываем в пул диспетчеру полученные роутеров экшины
					->push ($route->actions ())
					// Запускаем цикл диспетчеризации
					->dispatchCircle ()
					// Результат диспетчеризации
					->results ()
			);
		}
		catch (Zend_Exception $e)
		{
			Loader::load ('Error');
			Error::render ($e);
		}
	}
	
}
