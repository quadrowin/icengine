<?php

/**
 * Модель консольного пользователя
 * 
 * @author goorus, morph
 * @Service("userCli")
 */
class User_Cli extends User
{
    /**
     * @inheritdoc
     */
	protected static $config = array(
		'fields'	=> array (
			'id'		=> -1,
			'active'	=> 1,
			'login'		=> '',
			'email'		=> '',
			'password'	=> ''
		)
	);

	/**
	 * Создает и возвращает экземпляр модели консольного пользователя
	 * 
     * @return User_Cli
	 */
	public function getInstance()
	{
        $fields = array_keys(
            $this->getService('modelScheme')->scheme('User')->fields
                ->__toArray()
        );
        $configFields = $this->config()->fields;
        $resultFields = array();
        foreach ($fields as $fieldName) {
            $resultFields[$fieldName] = isset($configFields[$fieldName])
                ? $configFields[$fieldName] : null;
        }
		return new static($resultFields);
	}
    
    /**
     * @inheritdoc
     */
    public function hasRole($role)
    {
        return false;
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
        $this->getService('user')->setCurrent($instance);
	}

	/**
	 * @inheritdoc
	 */
	public function table()
	{
		return 'User';
	}
}