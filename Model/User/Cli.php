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
	protected static $_config = array(
		/**
		 * @desc Конфиг пользователя
		 * @var array
		 */
		'data'	=> array (
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
	protected static $instance;

	/**
	 * Создает и возвращает экземпляр модели консольного пользователя
	 * 
     * @return User_Cli
	 */
	public static function getInstance()
	{
		if (!self::$instance) {
			self::$instance = new self(static::config ()->data->__toArray());
		}
		return self::$instance;
	}

	/**
	 * Инициализирует модель гостя. Модель будет добавлена в менеджер ресурсов
     * 
	 * @param mixed $session_id Идентификатор сессии. Не имеет значения,
	 * параметр необходим для совместимости с User::init ().
	 */
	public static function init($sessionId = null)
	{
		$instance = self::getInstance();
		Resource_Manager::set('Model', $instance->resourceKey(), $instance);
	}

	/**
	 * @inheritdoc
	 */
	public function table()
	{
		return 'User';
	}
}