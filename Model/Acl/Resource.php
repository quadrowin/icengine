<?php

class Acl_Resource extends Model
{
	
    /**
     * Разделитель составных частей имени ресурса.
     * @var string
     */
	const NAME_PART_DELIM = '\\';
	
	/**
	 * Роли, имеющие доступ
	 * @var Acl_Role_Collection
	 */
	public $_roles;
	
	/**
	 * Получает ресурс по алиасу
	 * @param string $alias
	 * @return Acl_Resource
	 */
	public static function byAlias ($alias)
	{
	    return IcEngine::$modelManager->modelBy (
	        'Acl_Resource',
	        Query::instance ()
	        ->where ('alias', $alias)
	    );
	}
	
	/**
	 * 
	 * @param string|array $name
	 * @param $autocreate
	 * @return Acl_Resource
	 */
	public static function byNameAuto ($name)
	{
	    $name = is_array ($name) ? 
	        implode (self::NAME_PART_DELIM, $name) :
	        implode (self::NAME_PART_DELIM, func_get_args ());
	        
	    if (empty ($name))
		{
			throw new Exception ('Empty resource name.');
		}
		
		$resource = self::byNameCheck ($name);

		if (!$resource)
		{
			$resource = new Acl_Resource (array (
				'name'		=> $name
			));
			
			return $resource->save ();
		}
		
		return $resource;
	}
	
	/**
	 * 
	 * @param string|array $name
	 * @return Acl_Resource
	 */
	public static function byNameCheck ($name)
	{
	    $name = is_array ($name) ? 
	        implode (self::NAME_PART_DELIM, $name) :
	        implode (self::NAME_PART_DELIM, func_get_args ());

		if (empty ($name))
		{
			return null;
		}
		
		return IcEngine::$modelManager->modelBy (
		    __CLASS__,
		    Query::instance ()
		    ->where ('name', $name)
		);
	}
	
	/**
	 * @desc Создать несколько ресурсов
	 * @param array $names
	 * @param array $add_names
	 * @return array
	 */
	public static function create (array $names, array $add_names)
	{
		$resources = array ();
		
		$names = implode (self::NAME_PART_DELIM, $names);
		
		foreach ($add_names as $name)
		{
			$resources [] = self::byNameAuto (
				$names,
				$name
			);
		}
		
		return $resources;
	}
	
	/**
	 * @return Acl_Role_Collection
	 */
	public function roles ()
	{
		Loader::load ('Helper_Link');
		if (!$this->_roles)
		{
		    $this->_roles = Helper_Link::linkedItems ($this, 'Acl_Role');
		}
		
		return $this->_roles;
	}
	
	/**
	 * Имеет ли пользователь доступ
	 * @param User $user
	 * 		Пользователь
	 * @return boolean
	 */
	public function userCan (User $user)
	{
		return $user->isAdmin () || $this->roles ()->userAttached ($user);
	}
	
}