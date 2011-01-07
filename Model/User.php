<?php

if (!defined ('RESOURCE_ALLOW_ALL'))
{
	define ('RESOURCE_ALLOW_ALL', false);
}

class User extends Model
{
	
	/**
	 * Текущий пользователь.
	 * @var User
	 */
	protected static $_current    = false;
	
	/**
	 * Сессия
	 * @var User_Session
	 */
	protected $_session    = false;
    
	public static $scheme = array (
		Query::FROM	    => __CLASS__,
		Query::INDEX	=> array (
			array ('name'),
			array ('email'),
			array ('email', 'password')
		)
	);
	
	/**
	 * Авторизоваться этим пользователем.
	 * @return User
	 */
	public function authorize ()
	{
	    User_Session::getCurrent ()->User__id = $this->id;
	    User_Session::getCurrent ()->updateSession ();
	    self::$_current = $this;
		return $this;
	}

	/**
	 * @return boolean
	 */
	public static function authorized ()
	{
		return (bool) self::id ();
	}
	
	/**
	 * 
	 * @param string|integer $alias
	 * 		Алиас или id ресурса
	 * @return boolean
	 */
	public function can ($alias)
	{
		// Хак на время настройки - разрешить всем и всё.
		if (RESOURCE_ALLOW_ALL)
		{
			return true;
		}
		
		if (is_numeric ($alias))
		{
			$resource = IcEngine::$modelManager->get ('Acl_Resource', $alias);
		}
		else
		{
			$resource = IcEngine::$modelManager->modelBy (
				'Acl_Resource',
			    Query::instance ()
			        ->where ('alias', $alias)
			);
		}
		
		if (!$resource)
		{
		    return false;
		}
		
		return $resource->userCan ($this);
	}
	
	/**
	 * 
	 * @param string $email
	 * @param string $password
	 * @param integer $active
	 * @param array $exts
	 * 		Дополнительные поля
	 * @return User
	 */
	public static function create ($email, $password, $active, 
	    array $exts = array ())
	{
	    $exts = array_merge (
	        $exts,
	        array (
    			'email'		=> $email,
    			'password'	=> $password,
    			'active'	=> (int) $active
    		)
	    );
		$user = new User ($exts);
		
		return $user->save ();
	}
	
	/**
	 * Генерация пароля заданной длинны.
	 * @param integer $length
	 * @return string
	 */
	public static function genPassword ($length = 8)
	{
	    $result = substr (md5 (time), 0, $length);
	}
	
	/**
	 * @return User
	 */
	public static function getCurrent ()
	{
	    return self::$_current;
	}
	
	/**
	 * @return integer 
	 * 		id текущего пользователя.
	 */
	public static function id ()
	{
	    if (!self::$_current || !self::$_current->id)
	    {
		    return 0;
	    }
	    
	    return self::$_current->id;
	}

	/**
	 * Проверка принадлежности пользователя ролям.
	 *
	 * @param Acl_Role $role Названия ролей
	 * @return boolean
	 * 		Относится ли пользователь хотя бы к одной из ролей
	 */
	public function hasRole (Acl_Role $role)
	{
		return Helper_Link::wereLinked ($this, $role);
	}
	
	/**
	 * @dest Проверяет имеет ли пользователь роль с указаным типом
	 * @param integer $type_id
	 */
	public function hasRoleWithType ($type_id)
	{
		Loader::load ('Helper_Link');
		$collection = Helper_Link::linkedItems (
			$this,
			'Acl_Role'
		);
		$collection
			->where ('Acl_Role_Type__id=?', $type_id);
		return !$collection->isEmpty ();
	}
	
	/**
	 * 
	 * @param string $session_id Идентификатор сессии
	 * @return User
	 */
	public static function init ($session_id = null)
	{
	    $session_id = $session_id ? $session_id : Request::sessionId ();
	    User_Session::setCurrent (
	        User_Session::byPhpSessionId (
	            $session_id ? $session_id : 'unknown')
	    );
	    
	    self::$_current = User_Session::getCurrent ()->User;
		User_Session::getCurrent ()->updateSession ();
		
		return self::$_current;
	}
	
	public function logout ()
	{
		$this->_session->update (array (
		    'User__id'	=> 0
		));
	}
	
	/**
	 * @return Acl_Role
	 */
	public function personalRole ()
	{
	    Loader::load ('Acl_Role_Type_Personal');
	    
	    $role_name = 'User' . $this->id . 'Personal';
	    
	    $role = Acl_Role::byTypeNName (
	        Acl_Role_Type_Personal::ID,
	        $role_name
	    );
	    
	    if (!$role)
	    {
	        $role = new Acl_Role (array (
	            'name'	            => $role_name,
	            'Acl_Role_Type__id'	=> Acl_Role_Type_Personal::ID
	        ));
	        $role->save ();
	    }
	    
	    return $role;
	}
	
	/**
	 * 
	 * @param integer $role_type_id
	 * @param boolean $autocreate
	 * @return Acl_Role
	 */
	public function role ($role_type_id, $autocreate = false)
	{
		Loader::load ('Helper_Link');
		$role = Helper_Link::linkedItems (
			$this,
			'Acl_Role'
		);
		
		if ($role)
		{
			$role
				->addOptions (array (
					'name'			=> 'Role_Type',
					'role_type_id'	=> $role_type_id
				));
			$role = $role->first ();
		}
	    
	    if (!$role && $autocreate)
	    {
	        $role = new Acl_Role (array (
	            'name'				=> 'u' . $this->id . 'rt' . $role_type_id,
	            'Acl_Role_Type__id'	=> $role_type_id
	        ));
	        $role->save ();
	        Helper_Link::link ($role, $this);
	    }
	    
	    return $role;
	}
	
}

Model_Scheme::add ('User', User::$scheme);
Loader::load ('User_Guest');
Loader::load ('User_Session');