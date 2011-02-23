<?php
/**
 * 
 * @desc Модель гостя (незарегистрированного посетителя сайта).
 * @author Юрий
 * @package IcEngine
 *
 */
class User_Guest extends User
{
    
	/**
	 * @desc Экзмепляр помели гостя
	 * @var Model
	 */
    protected static $_instance;
    
    /**
     * (non-PHPdoc)
     * @see Model::_afterConstruct()
     */
    protected function _afterConstruct ()
    {
        $this->_loaded = true;
    }
    
    /**
     * @desc Создает и возвращает экземпляр модели гостя.
     * @return User_Guest
     */
    public static function getInstance ()
    {
        if (!self::$_instance)
        {
            self::$_instance = new self (array (
                'id'	    => 0,
                'active'	=> 1,
                'name'		=> '',
                'email'	    => '',
                'password'	=> ''
            ));
        }
        return self::$_instance;
    }
    
    /**
     * (non-PHPdoc)
     * @see Model::modelName()
     */
    public function modelName ()
    {
        return 'User';
    }
    
}

$instance = User_Guest::getInstance ();
Resource_Manager::set ('Model', $instance->resourceKey (), $instance);