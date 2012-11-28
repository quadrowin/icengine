<?php

/**
 * Модель консольного пользователя
 * @author goorus, morph
 */
class User_Cli extends User
{
    /**
     * @inheritdoc
     */
	protected static $config = array(
		/**
		 * @desc Конфиг пользователя
		 * @var array
		 */
		'fields'	=> array (
			'id'		=> -1,
			'active'	=> 1,
			'login'		=> '',
			'name'		=> '',
			'email'		=> '',
			'password'	=> ''
		)
	);

	/**
	 * Экзмепляр модели консольного пользователя
	 * 
     * @var User_Cli
	 */
	protected $instance;

	/**
	 * Создает и возвращает экземпляр модели консольного пользователя
	 * 
     * @return User_Cli
	 */
	public function getInstance()
	{
		if (!$this->instance) {
			$this->instance = new self($this->config()->fields->__toArray());
		}
		return $this->instance;
	}

	/**
	 * Инициализирует модель гостя. Модель будет добавлена в менеджер ресурсов
     * 
	 * @param mixed $session_id Идентификатор сессии. Не имеет значения,
	 * параметр необходим для совместимости с User::init ().
	 */
	public function init($sessionId = null)
	{
		$instance = $this->getInstance();
        $resourceManager = $this->getService('resourceManager');
		$resourceManager->set('Model', $instance->resourceKey(), $instance);
	}

	/**
	 * @inheritdoc
	 */
	public function table()
	{
		return 'User';
	}
}