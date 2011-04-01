<?php
/**
 * 
 * @desc Менеджер фоновых процессов.
 * @author Yury Shvedov
 * @package IcEngine
 *
 */
class Background_Agent_Manager
{
	
	/**
	 * @desc Конфиг
	 * @var array
	 */
	protected $_config = array (
	
		/**
		 * @desc Время в секундах после последней активности процесса,
		 * после которого состояние процесса будет установлено в ERROR.
		 * @var integer
		 */
		'process_to_error_time'		=> 300,
	
		/**
		 * @desc Время в секундах после последней активности процесса,
		 * после которого процесс будет перезапущен
		 * @var integer
		 */
		'process_to_restart_time'	=> 600,
	);
	
	public function __construct ()
	{
		
	}
	
	/**
	 * @desc Проверка "зависших" процессов
	 * @return integer
	 * 		Количество "зависших" процессов
	 */
	public function checkErrors ()
	{
		$time_limit = (int) $this->config ()->process_to_error_time;
		
		//Loader::load ('Background_Agent_Collection_Option');
		Loader::load ('Background_Agent_Session_Collection');
		$sessions = new Background_Agent_Session_Collection ();
		$sessions->addOptions (array (
			array (
				'name'	  	  => 'processExpiration',
				'time_limit'  => $time_limit
			)
		));
		$agents->update (array (
			'state'	=> Helper_Process::FAIL
		));
		return $agents->count ();
	}
	
	/**
	 * @desc Перезапуск процессов, помеченных как зависшие
	 * @return integer
	 * 		Количество перезапущенных процессов
	 */
	public function checkRestarts ()
	{
		$time_limit = (int) $this->config ()->process_to_restart_time;
		
		//Loader::load ('Background_Agent_Collection_Option');
		$agents = new Background_Agent_Collection ();
		$agents->addOptions (array (
			array (
				'name'			=> 'restartExpiration',
				'time_limit'	=> $time_limit
			)
		));
		foreach ($agents as $agent)
		{
			/**
			 * @var Background_Agent
			 */
			$agent->resetState ();
			$this->resumeSession ($agent);
		}
		return $agents->count ();
	}
	
	/**
	 * @desc Загружает и возвращает конфиг
	 * @return Objective
	 */
	public function config ()
	{
		if (is_array ($this->_config))
		{
			$this->_config = Config_Manager::get (__CLASS__, $this->_config);
		}
		return $this->_config;
	}
	
	/**
	 * @desc Перезапустить агента
	 * @param Background_Agent_Session $session
	 */
	public function resumeSession (Background_Agent_Session $session)
	{
		$session->Resume->resume ($session);
	}
	
	/**
	 * @desc Запуск фонового агента
	 * @param string $name Название.
	 * @param array $params Параметры.
	 */
	public function startAgent ($name, array $params = array ())
	{
		$agent = Model_Manager::modelBy (
			'Background_Agent',
			Query::instance ()
				->where ('name', $name)
		);
		
		Loader::load ('Background_Agent_Session');
		
		
		$session = new Background_Agent_Session (array (
			'Background_Agent__id'	=> $agent->id,
			'startTime'				=> Helper_Date::toUnix (),
			'iteration'				=> 0,
			'finishTime'			=> Helper_Date::toUnix (),
			'updateTime'			=> Helper_Date::toUnix (),
			'params'				=> json_encode ($params),
			'state'					=> Helper_Process::NONE
		));

		$session->start ();
		$this->resumeSession ($agent);
	}
	
}