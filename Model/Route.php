<?php

Loader::load ('Model_Child');

class Route extends Model_Child
{
    
    const MODEL_METHOD_GET_LINK = 'getRouteLink';
    const MODEL_METHOD_GET_SIBLINGS = 'getRouteSiblings';
    const MODEL_METHOD_GET_TITLE = 'getRouteTitle';
    
    /**
     * Получение ссылки на роут
     * @return string
     */
    public function link ()
    {
        if (
            $this->model && Loader::load ($this->model) &&
            method_exists ($this->model, self::MODEL_METHOD_GET_LINK)
        )
        {
             return call_user_func (
                 array ($this->model, self::MODEL_METHOD_GET_LINK),
                 $this
             );
        }
        
        $route = '/';
        $parts = trim ($this->route, '\\/');
        
        if (!$parts)
        {
            return '/';
        }
        
        $parts = explode ('/', $parts);
        
        foreach ($parts as $part)
        {
            $params = explode (':', $part);
            if (count ($params) > 1)
            {
                $route .= Request::param ($params [1]) . '/';
            }
            else
            {
                $route .= $part . '/';
            }
        }
        
        return $route;
    }
    
    /**
     * Формирует ссылку на страницу до части, включающей $stop_key.
     * Значение для части $stop_key берется из текущего адреса, либо
     * может быть передано вторым параметром
     * 
     * @param string $stop_key
     * 		Стоповый параметр
     * @param mixed $value [optional]
     * 		Значение для стопового параметра
     * @return string
     */
    public function linkPart ($stop_key)
    {
        if (func_num_args () > 1)
        {
            $stop_value = func_get_arg (1);
        }
        else
        {
            $stop_value = Request::param ($stop_key);
        }
        
        $route = trim ($this->route, '\\/');
        
        if (!$route)
        {
            return '/';
        }
        
        $link = '/';
        $route = explode ('/', $route);
        foreach ($route as $part)
        {
            $params = explode (':', $part);
            if (count ($params) > 1)
            {
                 if (array_search ($stop_key, $params))
                 {
                     $link .= $stop_value . '/';
                     break;
                 }
                 else
                 {
                     $link .= Request::param ($params [1]) . '/';
                 }
            }
            else
            {
                $link .= $part . '/';
            }
        }
        
        return $link;
    }
	
	/**
	 * 
	 * @return Route_Action_Collection
	 */
	public function actions ()
	{
	    return $this->modelManager ()->collectionBy (
	        'Route_Action',
	        Query::instance ()
	        ->where ('Route__id', $this->key ())
	        ->order ('sort')
	    );
	    
		$query = Query::instance ()
		    ->select (array (
				'route_action'	=> array ('sort', 'assign'),
				'controller_action'	=> array ('id', 'controller', 'action')
			))
			->from ('Route_Action', 'route_action')
			->innerJoin (
				array ('Controller_Action' => 'controller_action'),
				'controller_action.id=route_action.Controller_Action__id'
			)
			->where ('route_action.Route__id', $this->key ())
			->where ('controller_action.active=1')
			->order (array ('route_action.sort' => Query::ASC));
		
		Loader::load ('Controller_Action_Collection');
		
		$collection = new Controller_Action_Collection ();
		$collection->fromQuery ($query);
		//$query->translate ('Mysql', DDS::getDataSource()->getDataMapper()->getModelScheme());
		
		return $collection;
	}
	
	/**
	 * Заголовок части хлебной крошки.
	 * @return string
	 */
    public function title ()
    {
        if (
            $this->model && Loader::load ($this->model) &&
            method_exists ($this->model, self::MODEL_METHOD_GET_TITLE)
        )
        {
             return call_user_func (
                 array ($this->model, self::MODEL_METHOD_GET_TITLE),
                 $this
             );
        }
        return $this->title;
    }
	
    /**
     * 
     * @return array
     */
    public function siblings ()
    {
        if (
            $this->model && Loader::load ($this->model) &&
            method_exists ($this->model, self::MODEL_METHOD_GET_SIBLINGS)
        )
        {
            return call_user_func (
                array ($this->model, self::MODEL_METHOD_GET_SIBLINGS),
                $this
            );
        }
        
        $siblings = new Route_Collection ();
        $siblings
            ->where ('parentId=?', $this->parentId)
            ->where ('id!=?', $this->id)
            ->where ('visible=1')
            ->where ('active=1');
        
        $result = array ();
            
        foreach ($siblings as $sibling)
        {
            $result [] = array (
                'title'	=> $sibling->title,
                'link'	=> $sibling->link ()
            );
        }
        
        return $result;
    }
    
}