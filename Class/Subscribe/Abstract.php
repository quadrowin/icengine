<?php

abstract class Subscribe_Abstract
{
	/**
	 * 
	 * @var Background_Process
	 */
	protected $_daemon;
	
	/**
	 * 
	 * @var array
	 */
	protected $_data;
	
	/**
	 * 
	 * @var Subscribe_Provider_Abstract
	 */
	protected $_provider;
	
	/**
	 * 
	 * @var Subscribe_Render_Abstract
	 */
	protected $_render;
	
	public function __construct ()
	{
		$this->_data = array ();
	}
	
	/**
	 * @return array
	 */
	public function get ()
	{
		return array ();
	}
	
	/**
	 * @return Background_Process
	 */
	public function getDaemon ()
	{
		return $this->_daemon;
	}
	
	/**
	 * @return array
	 */
	public function getData ()
	{
		return $this->_data;
	}
	
	/**
	 * @return Subscribe_Provider_Abstract
	 */
	public function getProvider ()
	{
		return $this->_provider;
	}
	
	/**
	 * @return Sibscribe_Render_Abstract
	 */
	public function getRender ()
	{
		return $this->_render;
	}
	
	/**
	 * @return array <Subscriber_Subscriber>
	 */
	public function getSubscribers ()
	{
		Loader::load ('Subscribe_Subscriber_Collection');
		$collection = new Subscribe_Subscriber_Collection ();
		return $collection
			->items ();
	}
	
	/**
	 * @return string
	 */
	public function render ()
	{
		if (!is_null ($this->_render))
		{
			return $this->_render->render ($this->_data);
		}
	}

	public function run ()
	{
		$this->_data = $this->_get ();
		$this->_daemon->run (
			array ($this, 'send')
		);	
	}
	
	/**
	 * @return Subscribe_Abstract
	 */
	public function send ()
	{
		if (!is_null ($this->_provider))
		{
			$content = $this->render ();
			if ($content)
			{
				$this->_provider->send (
					$this->getSubscribers (),
					$content,
					$this->config
				);
			}
		}
		return $this;
	}
	
	/**
	 * 
	 * @param Background_Process $daemon
	 * @return Abstract
	 */
	public function setDaemon (Background_Process $daemon)
	{
		$this->_daemon = $daemon;
		return $this;
	}
	
	/**
	 * 
	 * @param Subscribe_Provider_Abstract $provider
	 * @return Abstract
	 */
	public function setProvider (Subscribe_Provider_Abstract $provider)
	{
		$this->_provider = $provider;
		return $this;
	}
	
	/**
	 * 
	 * @param Subscribe_Render_Abstract $render
	 * @return Abstract
	 */
	public function setRender (Subscribe_Render_Abstract $render)
	{
		$this->_render = $render;
		return $this;
	}

}