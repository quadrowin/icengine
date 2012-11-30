<?php

/**
 * Абстрактная модель пользователя.
 * 
 * @author goorus, morph
 */
class User_Abstract extends Model
{
	/**
	 * @inheritdoc
	 */
	protected static $config = array(
		// колбэк после авторизации
		'login_callback'	=> null,
		// функция, вызываемая при логауте.
		'logout_callback'	=> null
	);

	/**
	 * Текущий пользователь.
	 * 
     * @var User
	 */
	protected $current;

	/**
	 * Авторизоваться этим пользователем.
	 * 
     * @return User
	 */
	public function authorize()
	{
        $session = $this->getService('userSession');
		$session->updateSession($this->key());
		$this->current = $this;
		return $this;
	}

	/**
	 * Проверяет, авторизован ли пользователь.
	 * 
     * @return boolean True, если пользователь авторизован, иначе false.
	 */
	public function authorized ()
	{
		return (bool) $this->current;
	}

	/**
	 * Проверяет, имеет ли пользователь доступ.
	 * 
     * @param string|integer $name
	 * 		Алиас или id ресурса
	 * @return boolean
	 */
	public function can($name)
	{
        $modelManager = $this->getService('modelManager');
		if (is_numeric($name)) {
			$resource = $modelManager->get('Acl_Resource', $name);
		} else {
			$resource = $modelManager->byOptions(
				'Acl_Resource',
                array(
                    'name'  => '::Name',
                    'value' => $name
                )
			);
		}
		if (!$resource) {
			return false;
		}
		return $resource->userCan($this);
	}

	/**
	 * Создание пользователя.
	 * 
     * @param array|Objective $data Данные пользователя.
	 * $param ['email'] Емейл
	 * $param ['password'] Пароль
	 * $param ['active'] = 0 Активен
	 * $param ['ip'] IP пользователя при регистрации
	 * @return User
	 */
	public function create($data)
	{
		if (is_object($data)) {
			$data = $data->__toArray();
		}
		if (!isset($data['ip'])) {
			$data['ip'] = $this->getService('request')->ip();
		}
		$user = new User($data);
		return $user->save();
	}

	/**
	 * Возвращает модель текущего пользователя.
	 * Если пользователь не авторизован, будет возвращает экземпляр User_Guest.
	 * 
     * @return User Текущий пользователь.
	 */
	public function getCurrent()
	{
		return $this->current;
	}

	/**
	 * Возвращает id текущего пользователя.
	 * 
     * @return integer id текущего пользователя.
	 */
	public function id()
	{
		if (!$this->current || !$this->current->key()) {
			return 0;
		}
		return $this->current->key();
	}

	/**
	 * Проверяет, имеет ли пользователь роль админа.
	 * 
     * @return boolean true, если имеет, иначе false.
	 */
	public function isAdmin()
	{
		return $this->hasRole('admin');
	}

	/**
	 * Проверяет, является ли этот пользователем текущим.
	 * Т.е. авторизован от имени этого пользователя.
	 * 
     * @return boolean
	 */
	public function isCurrent()
	{
		return $this->authorized() && ($this->id() == $this->key());
	}

	/**
	 * Проверяет, имеет ли пользователь хотя бы одну из указанных ролей.
	 * 
     * @param Acl_Role|string $role Роль или название роли
	 * @param $_
	 * @return boolean Имеет ли пользователь роль.
	 */
	public function hasRole($role)
	{
        $roleNames = array();
        $roles = array();
		foreach (func_get_args() as $role) {
			if (!$role) {
                continue;
            }
            if (!is_object($role)) {
                $roleNames[] = $role;
            } else {
                $roles[] = $role;
            }
		}
        if ($roleNames) {
            $collectionManager = $this->getService('collectionManager');
            $roleCollection = $collectionManager->create('Acl_Role')
                ->addOptions(array(
                    'name'  => '::Name',
                    'value' => $roleNames
                ));
            if ($roleCollection->count()) {
                $roles = array_merge($roles, $roleCollection->items());
            }
        }
        $roleIds = $this->getService('helperArray')->column($roles, 'id');
        if (!$roleIds) {
            return false;
        }
        $queryBuilder = $this->getService('query');
        $query = $queryBuilder
            ->select('id')
            ->from('Link')
            ->where('fromTable', 'Acl_Role')
            ->where('toTable', 'User')
            ->where('fromRowId', $roleIds)
            ->where('toRowId', $this->key());
        $dds = $this->getService('dds');
        $exists = (bool) $dds->execute($query)->getResult()->asValue();
		return $exists;
	}

	/**
	 * Инициализация пользователя.
	 * Создание моделей сессии и пользователя.
	 * 
     * @param string $session_id Идентификатор сессии.
	 * @return User Пользователь.
	 */
	public function init($sessionId = null)
	{
        if ($this->current) {
            return;
        }
        $request = $this->getService('request');
		$sessionId = $sessionId ?:$request->sessionId();
        $session = $this->getService('session');
        $userSession = $session->byPhpSessionId($sessionId ?: 'unknown');
        $session->setCurrent($userSession);
		$this->current = $session->getCurrent()->User;
		$session->getCurrent()->updateSession();
		return $this->current;
	}

	/**
	 * Логаут. Удаление сессии.
	 */
	public function logout()
	{
		$session = $this->getService('session');
		$session->getCurrent()->delete();
	}

    /**
     *  @inheritodc
     */
	public function title()
	{
		return $this->login . ' ' . $this->name;
	}

    /**
     * Изменить текущего пользователя
     * 
     * @param type $user
     */
	public function setCurrent($user)
	{
		$this->current = $user;
	}
}