<?php
/**
 * 
 * @desc Контроллер для авторизации по емейлу, паролю и смс.
 * Предназначен для авторизации контентов в админке, поэтому 
 * сверяет данные из БД с данными из файла конфига.
 * @author Юрий Шведов
 * @package IcEngine
 *
 */
class Controller_Authorization_Login_Password_Sms extends Controller_Abstract
{
	
	/**
	 * @param Аттрибут с кодом, высланным в СМС
	 * @var string
	 */
	const SMS_CODE_ATTR = 'smsAuthCode';
	
	/**
	 * @param Аттрибут - количество отправленных СМС
	 * @var string
	 */
	const SMS_SEND_COUNTER_ATTR = 'smsAuthSendCount';
	
	/**
	 * @param Аттрибут со временем последней отправки кода
	 * @var string
	 */
	const SMS_SEND_TIME_ATTR = 'smsAuthSendTime';
	
	/**
	 * @desc Конфиг
	 * @var array
	 */
	protected $_config = array (
		// Лимит смс в 1 минуту
		'sms_send_limit_1m'			=> 10,
		
		// Лимит смс на 10 минут
		'sms_send_limit_10m'		=> 50
	);
	
	/**
	 * @desc Вовзращает модель авторизации.
	 * @return Authorization_Login_Password_Sms
	 */
	protected function _authorization ()
	{
		return Model_Manager::modelBy (
			'Authorization',
			Query::instance ()
				->where ('name', 'Login_Password_Sms')
		);
	}
	
	/**
	 * (non-PHPdoc)
	 * @see Controller_Abstract::index()
	 */
	public function index ()
	{
		// Просто форма авторизации
	}
	
	/**
	 * @desc Авторизация
	 * @param string $name Емейл пользователя 
	 * @param string $pass Пароль
	 * @param string $code Код активации из СМС
	 */
	public function login ()
	{
		list (
			$login,
			$password,
			$activation_id,
			$activation_code,
			$redirect
		) = $this->_input->receive (
			'name',
			'pass',
			'a_id',
			'code',
			'href'
		);
		
		if (!$activation_id || !$activation_code)
		{
			return $this->replaceAction ($this, 'sendSmsCode');
		}
		
		$user = $this->_authorization ()->authorize (array (
			'login'				=> $login,
			'password'			=> $password,
			'activation_id'		=> $activation_id,
			'activation_code'	=> $activation_code
		));
		
		if (!is_object ($user))
		{
			// Пользователя не существует
			$this->_sendError (
				'authorization error: ' . $user,
				$user ? $user : __METHOD__,
				$user ? null : '/passwordIncorrect'
			);
			return ;
		}
		
		// Сбрасываем счетчик СМС.
		$user->attr (array (
			self::SMS_SEND_COUNTER_ATTR	=> 0,
			self::SMS_CODE_ATTR			=> ''
		));
		
		Loader::load ('Helper_Uri');
		$redirect = Helper_Uri::validRedirect ($redirect);
		$this->_output->send (array (
			'redirect'		=> $redirect,
			'data'	=> array (
				'redirect'	=> $redirect
			)
		));
	}
	
	/**
	 * @desc Отправка СМС кода
	 */
	public function sendSmsCode ()
	{
		list (
			$login,
			$password
		) = $this->_input->receive (
			'name',
			'pass'
		);
		
		$user = Model_Manager::modelBy (
			'User',
			Query::instance ()
				->where ('login', $login)
				->where ('password', $password)
				->where ('md5(`password`) = md5(?)', $password)
		);
		
		if (!$user)
		{
			$this->_sendError (
				'password incorrect',
				'Data_Validator_Authorization_Password/invalid'
			);
			return ;
		}
		
		if (!$user->active)
		{
			$this->_sendError (
				'user unactive',
				'Data_Validator_Authorization_User/unactive'
			);
			return ;
		}
		
		if (!$user->phone)
		{
			$this->_sendError (
				'no phone',
				__METHOD__,
				'/noPhone'
			);
			return ;
		}
		
		$count = $user->attr (self::SMS_SEND_COUNTER_ATTR);
		$time = Helper_Date::toUnix ();
		$last_time = $user->attr (self::SMS_SEND_TIME_ATTR);
		$delta_time = Helper_Date::secondsBetween ($last_time);
		
		if (
			(
				$count >= $this->config ()->sms_send_limit_1m &&
				$delta_time < 60
			) ||
			(
				$count >= $this->config ()->sms_send_limit_10m &&
				$delta_time < 600
			)
		)
		{
			$this->_sendError (
				'sms limit',
				__METHOD__,
				'/smsLimit'
			);
			return ;
		}
		
		$activation = $this->_authorization ()->sendActivationSms (array (
			'login'		=> $login,
			'password'	=> $password,
			'phone'		=> $user->phone,
			'user'		=> $user
		));
		
		if (!is_object ($activation))
		{
			$this->_sendError (
				'send activation code fail',
				$activation ? activation : __METHOD__,
				$activation ? null : '/accessDenied'
			);
			return ;
		}
		
		$user->attr (array (
			self::SMS_SEND_TIME_ATTR		=> $time,
			self::SMS_SEND_COUNTER_ATTR		=> $count + 1
		));
		
		$this->_output->send (array (
			'activation'	=> $activation,
			'time'			=> $time,
			'data'			=> array (
				'activation_id'		=> $activation->id
			)
		));
	}
	
}