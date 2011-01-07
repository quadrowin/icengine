<?php

class Acl_Role extends Model
{
	
	const GUEST_ROLE_NAME	= 'guest';
	
	public static $scheme = array(
		Query::FROM	    => __CLASS__,
		Query::INDEX	=> array(
			array ('name'),
			array ('Acl_Role_Type__id'),
			array ('Acl_Role_Type__id', 'name')
		)
	);
	
	/**
	 * Предоставление роли права на ресурс.
	 * @param Acl_Resource $resource
	 * @return Acl_Role
	 */
	public function attachResource (Acl_Resource $resource)
	{
	    foreach (func_get_args () as $res)
	    {
	        Helper_Link::link ($this, $res);
	    }
	    return $this;
	}
	
	/**
	 * Добавление роли пользователю.
	 * @param User $user
	 * @return Acl_Role
	 */
	public function attachUser (User $user)                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                              
	{
	    Helper_Link::link ($this, $user);
		return $this;
	}
	
	/**
	 * 
	 * @param integer $type_id
	 * @param string $name
	 * @return Acl_Role
	 */
	public static function byTypeNName ($type_id, $name)
	{
		return IcEngine::$modelManager->modelBy (
		    'Acl_Role',
		    Query::instance ()
		    ->where ('Acl_Role_Type__id', $type_id)
		    ->where ('name', $name)
		);
	}
	
	/**
	 * Делает ресурс недоступным для роли
	 * @param Acl_Resource $resource
	 * @return Acl_Role
	 */
	public function deattachResource (Acl_Resource $resource)
	{
		return $this->unjoin ($resource);
	}
	
	/**
	 * Забирает роль у пользователя
	 * @param User $user
	 * @return Acl_Role
	 */
	public function deattachUser (User $user)
	{
		return $this->unjoin ($user);
	}
	
	/**
	 * Дает роли доступ к ресурсу.
	 * @param array $_ Ресурс
	 */
	public function grant ($_)
	{
		if (!is_array ($_))
		{
		    $_ = func_get_args ();
		}
		
		$resource = Acl_Resource::byNameParts ($_, true);
		
		$this->attachResource ($resource);
	}
	
	/**
	 * Имеет ли роль доступ к ресурсу
	 * @param array $_ Ресурс
	 */
	public function hasAccess ($_)
	{
		if (!is_array ($_))
		{
			$_ = func_get_args ();
		}
		
		$resource = Acl_Resource::byNameParts ($_);
		
		return $resource ? $this->resourceAttached ($resource) : false;
	}
	
	/**
	 * Имеет ли роль доступ к ресурсу.
	 * @param Acl_Resource $resource_id 
	 * @return boolean
	 */
	public function resourceAttached (Acl_Resource $resource)
	{
		return Helper_Link::wereLinked ($this, $resource);
	}
	
	/**
	 * Отнимает право на ресурс.
	 * @param array|string $_ Ресурс
	 */
	public function revoke ($_)
	{
		if (is_array ($_))
		{
			$resource = Acl_Resource::byNameParts ($_);
		}
		else
		{
			$resource = Acl_Resource::byNameParts (func_get_args ());
		}
		
		if ($resource)
		{
			$this->deattachResource ($resource);
		}
	}
	
	/**
	 * Проверяет, имеет ли пользователь эту роль.
	 * @param User $user
	 * @return boolean
	 */
	public function userAttached (User $user)
	{
	    return $this->Acl_Role_Type->isUserAttached ($user, $this);
    }
	
}

Model_Scheme::add ('Acl_Role', Acl_Role::$scheme);