<?php

/**
 * Модель гостя (незарегистрированного посетителя сайта).
 *
 * @author goorus, morph
 * @Service("userGuest")
 */
class User_Guest extends User_Cli
{
	/**
	 * @desc Конфиг
	 * @var array
	 */
	protected static $config = array(
		/**
		 * @desc Конфиг пользователя
		 * @var array
		 */
		'fields'	=> array(
			'id'		=> 0,
			'active'	=> 1,
			'login'		=> '',
			'name'		=> '',
			'email'		=> '',
			'password'	=> ''
		)
	);

    /**
	 * @inheritdoc
	 */
	public function init($sessionId = null)
	{
		$instance = $this->getInstance();
        $resourceManager = $this->getService('resourceManager');
        $provider = $this->getService('dataProviderManager')->get(
            'user_session'
        );
        if (isset($_COOKIE['PHPSESSID'])) {
            $sessionId = $_COOKIE['PHPSESSID'];
        } else {
            $sessionId = $this->getService('request')->sessionId();
        }
        $key = 'User_Session_1/0:' . $sessionId;
        $data = $provider->get($key);
        if ($data) {
            $session = new User_Session($data);
            $this->getService('session')->setCurrent($session);
            $this->getService('user')->setCurrent($instance);
        }
		$resourceManager->set('Model', $instance->resourceKey(), $instance);
	}
}