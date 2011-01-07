<?php

class Background_Agent_Manager
{
    
    public $config = array (
    
        /**
         * Время в секундах после последней активности процесса,
         * после которого состояние процесса будет установлено в ERROR.
         * @var integer
         */
        'process_to_error_time'     => 300,
    
        /**
         * Время в секундах после последней активности процесса,
         * после которого процесс будет перезапущен
         * @var integer
         */
        'process_to_restart_time'	=> 600,
    
        /**
         * Команда для перезапуска процесса
         * @var string
         */
        'resume_cmd'				=> 'php agents.php secret process {$agent_id}',
    
        /**
         * Адрес для перезапуска процесса
         * @var string
         */
        'resume_url'				=> 'vipgeo.ru/background/resume/?id={$agent_id}',
        
        /**
         * Корневой каталог (для запуска скриптов)
         */
        'root_directory'			=> ''
    );
    
    public function __construct ()
    {
        Loader::load ('Config_Php');
        $config = new Config_Php ('config/agents.php');
        $this->config = $config->mergeConfig ($this->config);
    }
    
    /**
     * Проверка "зависших" процессов
     * @return integer
     * 		Количество "зависших" процессов
     */
    public function checkErrors ()
    {
        $time_limit = (int) $this->config ['process_to_error_time'];
        
        //Loader::load ('Background_Agent_Collection_Option');
        $agents = new Background_Agent_Collection ();
        Loader::load ('Background_Agent_State');
        $agents->addOptions (array (
        	array (
                'name'	  	  => 'processExpiration',
        	    'time_limit'  => $time_limit
            )
        ));
        $agents->update (array (
            'Background_Agent_State__id'	=> Background_Agent_State::ERROR
        ));
        return $agents->count ();
    }
    
    /**
     * Перезапуск процессов, помеченных как зависшие
     * @return integer
     * 		Количество перезапущенных процессов
     */
    public function checkRestarts ()
    {
        $time_limit = (int) $this->config ['process_to_restart_time'];
        
        //Loader::load ('Background_Agent_Collection_Option');
        $agents = new Background_Agent_Collection ();
        $agents->addOptions (array (
            array (
                'name'		    => 'restartExpiration',
                'time_limit'	=> $time_limit
            )
        ));
        foreach ($agents as $agent)
        {
            /**
             * @var Background_Agent
             */
            $agent->resetState ();
            $this->resumeAgent ($agent);
        }
        return $agents->count ();
    }
    
    public function resumeAgent (Background_Agent $agent)
    {
        $type = $agent->Background_Agent_Type->name;
        
        if (!$type)
        {
            Loader::load ('Zend_Exception');
            throw new Zend_Exception ("Empty agent resume type.");
            return;
        }
        
        $method = 'resumeAgent' . $type;
    	$this->{$method} ($agent);
    }
    
    /**
     * Перезапуск агента через командную строку
     * @param Background_Agent $agent
     */
    public function resumeAgentCmd (Background_Agent $agent)
    {
        $values = array (
            '{$agent_id}'	=> $agent->id
        );
        
        $cmd = str_replace (
            array_keys ($values),
            array_values ($values),
            $this->config ['resume_cmd']
        );
        
	    chdir ($this->config ['root_directory']);
	    die (popen ($cmd, 'r'));
	    //exec ('start ' . $cmd);
	    //popen ($cmd, 'r');
	    //system ($cmd);
    }
    
    /**
     * Перезапуск агента через http
     * @param Background_Agent $agent
     */
    public function resumeAgentHttp (Background_Agent $agent)
    {
        $values = array (
            '{$agent_id}'	=> $agent->id
        );
        
        $url = str_replace (
            array_keys ($values),
            array_values ($values),
            $this->config ['resume_url']
        );
        
	    $url = parse_url ($url);
	    
		Common_Network::callUnresultedPage (
			isset ($url ['host']) ? $url ['host'] : $_SERVER ['SERVER_NAME'],
			$url ['path'],
			$url ['query']
		);
    }
    
}