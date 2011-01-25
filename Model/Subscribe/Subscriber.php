<?php

class Subscribe_Subscriber extends Model
{
    
    /**
     * 
     * @param string $email
     * @return Subscriber_Subscriber
     */
    public static function byEmail ($email)
    {
        $subscriber = IcEngine::$modelManager->modelBy (
            'Subscribe_Subscriber',
            Query::instance ()
            ->where ('email', $email)
        );
        
        if (!$subscriber)
        {
            $subscriber = new Subscribe_Subscriber (array (
                'active'	=> 1,
                'date'	    => date ('Y-m-d H:i:s'),
            	'email'	    => $email
            ));
            $subscriber->save ();
        }
        
        return $subscriber;
    }
    
    /**
     * 
     * @param Subscribe_Abstract|integer $subscribe
     * @return boolean
     */
    public function subscribed ($subscribe)
    {
        Loader::load ('Subscribe_Abstract');
        if (!is_a ($subscribe, 'Subscribe_Abstract'))
        {
            $subscribe = IcEngine::$modelManager->get ('Subscribe', 
                (int) $subscribe);
        }
        $join = $subscribe->subscriberJoin ($this);
        
        return $join ? (bool) $join->active : false;
    }
    
    /**
     * Возвращает время в секундах с последнего запроса.
     * @return integer
     */
    public function timeLeft ()
    {
        Loader::load ('Common_Date');
        return time - Helper_Date::strToTimestamp ($this->codeSendTime);
    }
    
}