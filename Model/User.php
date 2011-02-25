<?php

if (!defined ('RESOURCE_ALLOW_ALL'))
{
	define ('RESOURCE_ALLOW_ALL', false);
}
/**
 * 
 * Модель пользователя.
 * Для корректной работы необходима модель User_Session.
 * @author Юрий
 * @package IcEngine
 *
 */
class User extends Model
{
	
	/**
	 * Текущий пользователь.
	 * @var User
	 */
	protected static $_current	= false;
	
	/**
	 * Авторизоваться этим пользователем.
	 * @return User
	 */
	public function authorize ()
	{
		User_Session::getCurrent ()->updateSession ($this->id);
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
	 * @desc Создание пользователя.
	 * @param string $email
	 * @param string $password
	 * @param integer $active
	 * @param array|Objective $exts Дополнительные поля
	 * @return User
	 */
	public static function create ($email, $password, $active, 
		$exts = array ())
	{
		$exts = array_merge (
			is_array ($exts) ? $exts : $exts->__toArray (),
			array (
				'email'		=> $email,
				'password'	=> $password,
				'active'	=> (int) $active,
				'ip'		=> Request::ip ()
			)
		);
		$user = new User ($exts);
		
		return $user->save ();
	}
	
	/**
	 * @desc Генерация пароля заданной длинны.
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
	 * Проверяет, является ли этот пользователем текущим.
	 * Т.е. авторизован от имени этого пользователя.
	 * @return boolean
	 */
	public function isCurrent ()
	{
		return self::authorized () && (self::id () == $this->key ());
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
		if (!$role)
		{
			return false;
		}
		return Helper_Link::wereLinked ($this, $role);
	}
	
	/**
	 * @desc Проверяет имеет ли пользователь роль с указаным типом
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
	
	/**
	 * @desc Логаут.
	 * Удаление сессии.
	 */
	public function logout ()
	{
		User_Session::getCurrent ()->delete ();
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
				'name'				=> $role_name,
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

Loader::load ('User_Guest');
Loader::load ('User_Session');