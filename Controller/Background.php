<?php

class Controller_Background extends Controller_Abstract
{
	
    /**
     * 
     * @var Background_Agent_Manager
     */
    protected $_manager;
    
	/**
	 * 
	 * @return Background_Agent
	 */
	protected function _getAgent ()
	{
		$id = $this->_input->receive ('id');
		$agent = IcEngine::$modelManager->get ('Background_Agent', $id);
		if (!$agent)
		{
			$this->_output->send (array (
				'error'	=> 'Agent not found.'
			));
		}
		return $agent;
	}
	
	/**
	 * @return Background_Agent_Manager
	 */
	protected function _manager ()
	{
	    if (!$this->_manager)
	    {
	        Loader::load ('Background_Agent_Manager');
	        $this->_manager = new Background_Agent_Manager ();
	    }
	    return $this->_manager;
	}
	
	public function agents ()
	{
		Loader::load ('Background_Agent_Collection');
		$agents = new Background_Agent_Collection ();
		$this->_output->send (array (
			'agents'	=> $agents
		));
	}
	
	public function resetState ()
	{
		$agent = $this->_getAgent ();
		if (!$agent)
		{
			return;
		}
		
		$agent->resetState ();
	}
	
	public function resume ()
	{
		$agent = $this->_getAgent ();
		if (!$agent)
		{
			return;
		}
		
		$this->_manager ()->resumeAgent ($agent);
		die ();
	}
	
	public function stop ()
	{
		$agent = $this->_getAgent ();
		if (!$agent)
		{
			return;
		}
		
		$agent->stop ();
	}
	
}