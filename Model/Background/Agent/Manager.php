<?php

/**
 * Менеджер фоновых процессов.
 * 
 * @author goorus
 */
class Background_Agent_Manager extends Manager_Abstract
{
	/**
	 * @inheritdoc
	 */
	protected $config = array (

		//
		'default_agent_resume_id'	=> 0,

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

	/**
	 * Запуск рабочей итерации любой незавершенной сесси фонового
	 * агента заданного класса.
	 * 
     * @param string $name Название фонового агента.
	 */
	public function processAgent($name)
	{
        $modelManager = $this->getService('modelManager');
		$agent = $modelManager->byOptions(
            'Background_Agent',
			array(
                'name'  => '::Name',
                'value' => $name
            )
        );
		if (!$agent) {
			return;
		}
        $queryBuilder = $this->getService('query');
        $query = $queryBuilder
            ->where('Background_Agent__id', $agent->key())
			->where('state', Helper_Process::PAUSE);
		$session = $modelManager->byQuery($query);
		if ($session) {
			$session->process();
		} else {
			echo "no background agent sessions\n";
		}
	}

	/**
	 * Запуск фонового агента
	 * 
     * @param string $name Название.
	 * @param array $params Параметры.
	 */
	public function startAgent($name, array $params = array())
	{
		$modelManager = $this->getService('modelManager');
		$agent = $modelManager->byOptions(
            'Background_Agent',
			array(
                'name'  => '::Name',
                'value' => $name
            )
        );
		if (!$agent) {
			return;
		}
        $date = Helper_Date::toUnix();
		$session = $modelManager->create('Background_Agent_Session', array(
			'Background_Agent__id'  => $agent->key(),
			'startTime'             => $date,
			'iteration'             => 0,
			'finishTime'            => $date,
			'updateTime'            => $date,
			'params'                => json_encode($params),
			'state'                 => Helper_Process::NONE
		));
		$session->start();
	}
}