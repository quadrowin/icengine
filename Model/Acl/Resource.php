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
	
	public static $scheme = array (
		Query::FROM	    => __CLASS__,
		Query::INDEX	=> array (
			array ('alias'),
			array ('name')
		)
	);
	
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
		return $this->roles ()->userAttached ($user);
	}
	
}

Model_Scheme::add ('Acl_Resource', Acl_Resource::$scheme);