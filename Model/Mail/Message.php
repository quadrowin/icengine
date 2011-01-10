<?php

class Mail_Message extends Model
{
    
    const DEFAULT_FROM_NAME = 'Vipgeo';
    
    const DEFAULT_FROM_EMAIL = 'info@vipgeo.ru';
	
	public static $scheme = array (
		Query::FROM	=> __CLASS__
	);
	
	/**
	 * 
	 * @param string $template_name
	 * @param string $to_email
	 * @param string $to_name
	 * @param array $data
	 * @param integer $to_user_id
	 * @return Mail_Message_Item
	 */
	public static function create ($template_name, $to_email, $to_name, 
		array $data = array (), $to_user_id = 0)
	{
		Loader::load ('Mail_Template');
		$template = Mail_Template::byName ($template_name);
		
		$message = new Mail_Message (array (
			'Mail_Template__id'	=> $template->id,
			'toEmail'		    => $to_email,
			'toName'		    => $to_name,
		    'sendTries'			=> 0,
			'subject'		    => $template->subject ($data),
		    'time'				=> date ('Y-m-d H:i:s'),
			'body'			    => $template->body ($data),
			'toUserId'	        => (int) $to_user_id
		));
		
		return $message;
	}
	
	/**
	 * Попытка отправки сообщения
	 * @return boolean
	 */
	public function send ()
	{
	    Loader::load ('Common_Date');
		$this->update (array (
			'sendDay'		=> Helper_Date::eraDayNum (),
			'sendTime'		=> date ('Y-m-d H:i:s'),
			'sendTries'	    => $this->sendTries + 1
		));
		
		// TODO: Отправка письма
		Loader::load ('Mail_Provider_Mimemail');
		$provider = new Mail_Provider_Mimemail ();
		
		try
		{
    		return $provider->send (
    		    $this->toEmail,
    		    $this->body,
    		    array (
    		        'From'    => array (
    		            'name'	=> self::DEFAULT_FROM_NAME,
    		            'email'	=> self::DEFAULT_FROM_EMAIL
    		        ),
    		        'Subject'	=> $this->subject
    		    )
    		);
		}
		catch (Exception $e)
		{
		    Debug::logVar ($e, 'Sendmail error message');
		    return false;
		}
	}
	
}

Model_Scheme::add ('Mail_Message', Mail_Message::$scheme);