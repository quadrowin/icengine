<?php
/**
 * 
 * @desc Активация чего-либо, требующая код подтверждения от пользователя.
 * @author Юрий Шведов
 * @package IcEngine
 *
 */
class Activation extends Model
{
	
	/**
	 * @desc Время завершения по умолчанию.
	 * @var string
	 */
	const EMPTY_FINISH_TIME = '2000-01-01';
	
	/**
	 * @desc Находит активацию по коду, при условии, что она не 
	 * истекла по времени.
	 * @param string $code Код активации.
	 * @return Activation|null Найденная активация.
	 */
	public static function byCode ($code)
	{
		return IcEngine::$modelManager->modelBy (
			'Activation',
			Query::instance ()
			->where ('code', $code)
			->where ('expirationTime<', Helper_Date::toUnix ())
		);
	}
	
	/**
	 * @desc Создает и возвращает новую активацию.
	 * @param string $code Код активации.
	 * @param string $expirationTime Время, когда активация станет 
	 * 	недействительной (в формате UNIX).
	 * @param string $callback_message Сообщение, попадающие в Message_Queue
	 * 	после успешной активации.
	 * @return Activation Созданная активация.
	 */
	public static function create ($code, $expirationTime, 
		$callback_message = '')
	{
		$activation = new Activation (array (
			'code'				=> $code,
			'finished'			=> 0,
			'createTime'		=> Helper_Date::toUnix (),
			'finishTime'		=> self::EMPTY_FINISH_TIME,
			'User__id'			=> User::id (),
			'createIp'			=> Request::ip (),
			'finishIp'			=> '',
			'day'				=> Helper_Date::eraDayNum (),
			'callbackMessage'	=> $callback_message
		));
		return $activation->save ();
	}
	
	/**
	 * @desc Успешное окончание активации
	 * @return Activation Эта активация.
	 */
	public function activate ()
	{
		$this->update (array (
			'finished'		=> 1,
			'finishTime'	=> Helper_Date::toUnix (),
			'finishIp'		=> Request::ip ()
		));
		
		// Сообщение об успешной активации
		if ($this->callbackMessage)
		{
			IcEngine::$application->messageQueue->push (
				$this->callbackMessage,
				array (
					'activation'	=> $this
				)
			);
		}
		
		return $this;
	}
	
}