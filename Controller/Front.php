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
	 * @desc Название транспорта входа
	 * @var string
	 */
	const TRANSPORT_INPUT = 'input';
	
	/**
	 * @desc Запускаем фронт контролер.
	 */
	public function run ()
	{
		Loader::load ('Router');
		// Начинаем роутинг
		$route = Router::getRoute ();
		
		// Инициализируем вьюшник из запроса
		$view = View_Render_Manager::pushView (
			$route->viewRender ()
		);
		
		// Отправляем сообщение, что вью был изменен
		Loader::load ('Message_After_Router_View_Set');
		Message_After_Router_View_Set::push ($route, $view);
		
		try 
		{
			Loader::load ('Controller_Dispatcher');
			// Начинаем цикл диспетчеризации и получаем список
			// выполняемых экшинов
			$actions = Controller_Dispatcher::loop (
				$route->actions ()
			);
			
			// Направляем входные данные в целевые контроллеры
			$actions->applyTransport (
				self::TRANSPORT_INPUT,
				$this->_input
			);
			
			// Выполнение экшенов
			$results = Controller_Manager::runTasks (
				$actions
			);
			
			// Рендеринг
			$view->render ($results);
		}
		catch (Zend_Exception $e)
		{
			Loader::load ('Error');
			Error::render ($e);
		}
	}
	
}
