<?php

abstract class Subscribe_Abstract extends Model_Factory_Delegate
{
    
    const CONFIRM_SUBSCRIBE_TEMPLATE = 'subscribe_activate';
    
    const CONFIRM_UNSUBSCRIBE_TEMPLATE = 'subscribe_deactivate';
    
	/**
	 * 
	 * @var Background_Process
	 */
	protected $_daemon;
	
	/**
	 * 
	 * @var array
	 */
	protected $_data = array ();
	
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
	
	/**
	 * 
	 * @param stirng $code
	 * @return string
	 */
	protected function _confirmSubscribeHref ($code)
	{
	    return 'http://www.vipgeo.ru/subscribe/activate/' . $code;
	}
	
	/**
	 * 
	 * @param string $code
	 * @return string
	 */
	protected function _confirmUnsubscribeHref ($code)
	{
	    return 'http://www.vipgeo.ru/subscribe/deactivate/' . $code; 
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
	 * @return array
	 */
	public function getSubscribers ()
	{
		Loader::load ('Subscribe_Subscriber_Collection');
		$collection = new Subscribe_Subscriber_Collection ();
		return $collection
			->load ()
			->getItems ();
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
	 * @return Abstract
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
	 * @param Subscribe_Subscriber $subscriber
	 */
	public function sendSubscribeConfirmation (
	    Subscribe_Subscriber $subscriber)
	{
	    $join = self::subscriberJoin ($subscriber, true)->regenCode ();
	    
	    Loader::load ('Mail_Message');
	    $mail = Mail_Message::create (
	        self::CONFIRM_SUBSCRIBE_TEMPLATE,
	        $subscriber->email,
	        '',
	        array (
	            'code'          => $join->code,
	            'subscribe'	    => $this,
	            'subscriber'	=> $subscriber,
	            'href'		    => $this->_confirmSubscribeHref ($join->code)
	        )
	    );
	    
	    $mail->send ();
	}
	
	/**
	 * 
	 * @param Subscribe_Subscriber $subscriber
	 */
	public function sendUnsubscribeConfirmation (
	    Subscribe_Subscriber $subscriber)
	{
	    $join = self::subscriberJoin ($subscriber, true)->regenCode ();
	    
	    Loader::load ('Mail_Message');
	    $mail = Mail_Message::create (
	        self::CONFIRM_UNSUBSCRIBE_TEMPLATE,
	        $subscriber->email,
	        '',
	        array (
	            'code'          => $join->code,
	            'subscribe'	    => $this,
	            'subscriber'	=> $subscriber,
	            'href'		    => $this->_confirmUnsubscribeHref ($join->code)
	        )
	    );
	    
	    $mail->send ();
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
	
	/**
	 * 
	 * @param Subscribe_Subscriber $subscriber
	 * @param boolean $autocreate
	 * @return Subscribe_Subscriber_Join|null
	 */
	public function subscriberJoin (Subscribe_Subscriber $subscriber,
	    $autocreate = false)
	{
	    $join = $this->modelManager ()->modelBy (
	        'Subscribe_Subscriber_Join',
	        Query::instance ()
	        ->where ('Subscribe__id', $this->id)
	        ->where ('Subscribe_Subscriber__id', $subscriber->id)
	    );
	    
	    if (!$join && $autocreate)
	    {
	        Loader::load ('Subscribe_Subscriber_Join');
	        $join = new Subscribe_Subscriber_Join (array (
	            'Subscribe__id'               => $this->id,
	            'Subscribe_Subscriber__id'    => $subscriber->id,
	            'active'					  => 0,
	            'fromDate'				      => date ('Y-m-d H:i:s'),
	            'code'						  => ''
	        ));
	        $join->save ();
	    }
	    
	    return $join;
	}

}