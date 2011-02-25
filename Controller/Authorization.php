<?php
/**
 * 
 * @desc Контроллер авторизации
 * @author Юрий
 * @package IcEngine
 *
 */
class Controller_Authorization extends Controller_Abstract
{
    
	/**
	 * Редиректо по умолчанию после авторизация/логаута.
	 * @var unknown_type
	 */
    const DEFAULT_REDIRECT = '/';
	
    /**
     * Досутп закрыт для текущего пользователя
     */
	function accessDenied ()
	{
		$this->_output->send ('user', User::getCurrent ());
	}
	
	public function authDialog ()
	{
	
	}
	
	/**
	 * Проверка на существования пользователя с таким Email.
	 * Используется в диалоге входа/регистрации.
	 */
	public function checkEmail ()
	{
		$email = $this->_input->receive ('email');
		
		$exists = DDS::execute (
			Query::instance ()
			->select ('id')
			->from ('User')
			->where ('email', $email)
		)->getResult ()->asValue ();
		
		$this->_output->send ('data', array (
			'email'		=> $email,
			'exists'	=> (bool) $exists
		));
		
		$this->_dispatcherIteration->setTemplate (null);
	}
	
	/**
	 * @desc Авторизация.
	 * @param string login Логин.
	 * @param string password Пароль.
	 * @param string redirect [optional] Редирект после успешной авторизации.
	 */
	public function login ()
	{
		$login = $this->_input->receive ('login');
		$password = $this->_input->receive ('password');
		$redirect = $this->_input->receive ('redirect');
		
		Loader::load ('Helper_Uri');
		$redirect = Helper_Uri::validRedirect (
			$redirect ? $redirect : self::DEFAULT_REDIRECT
		);

		Loader::load ('Authorization');
		
		$user = Authorization::authorize ($login, $password);
		
		if ($user)
		{
			$this->_output->send ('data', array (
				'user'	=> array (
		            'id'	=> $user->id,
		            'name'	=> $user->name
		        ),
		        'redirect'	=> $redirect
		    ));
		}
		else
		{
		    $this->_output->send ('data', array (
		        'error'	=> 'Password incorrect'
		    ));
		    $this->_dispatcherIteration->setClassTpl (
		    	__METHOD__,
		    	'/password_incorrect.tpl'
		    );
		}
	}
	
	/**
	 * Авторизация или регистрация.
	 */
	public function loginOrReg ()
	{
		$login = $this->_input->receive ('login');
		
		$login_exists = DDS::execute (
			Query::instance ()
			->select ('id')
			->from ('User')
			->where ('email', $login)
		)->getResult ()->asValue ();
		
		if ($login_exists)
		{
			// Авторизация
			return $this->replaceAction ($this, 'login');
		}

		// Регистрация
		return $this->replaceAction ('Registration', 'postForm');
	}
	
	/**
	 * Выход.
	 */
	public function logout ()
	{
	    User_Session::getCurrent ()->delete ();
	    $redirect = $this->_input->receive ('redirect');
	    
	    Loader::load ('Helper_Uri');
	    $redirect = Helper_Uri::validRedirect (
	    	$redirect ? $redirect : self::DEFAULT_REDIRECT
	    );
	    
	    $this->_output->send ('data', array (
	    	'redirect'	=> $redirect
		));
	}
	
}