<?php

/**
 * Активация чего-либо, требующая код подтверждения от пользователя.
 *
 * @author goorus, morph
 * @Service("activation")
 * @Orm\Entity
 */
class Activation extends Model
{
	/**
	 * Время завершения по умолчанию.
	 * @var string
	 */
	const EMPTY_FINISH_TIME = '2000-01-01';

    /**
     * @Orm\Field\Int(Size=11, Not_Null, Auto_Increment)
     * @Orm\Index\Primary
     */
    public $id;

    /**
     * @Orm\Field\Varchar(Size=128, Not_Null)
     */
    public $address;

    /**
     * Сообщение
     *
     * @Orm\Field\Int(Size=11, Not_Null, Default=0)
     */
    public $Mail_Message__id;

    /**
     * @Orm\Field\Varchar(Size=128, Not_Null)
     */
    public $type;

    /**
     * @Orm\Field\Varchar(Size=128, Not_Null)
     */
    public $code;

    /**
     * @Orm\Field\Varchar(Size=128, Not_Null)
     */
    public $callbackMessage;

    /**
     * @Orm\Field\Int(Size=11, Not_Null)
     */
    public $finished;

    /**
     * @Orm\Field\Int(Size=11, Not_Null)
     */
    public $day;

    /**
     * @Orm\Field\Datetime(Not_Null)
     */
    public $createTime;

    /**
     * @Orm\Field\Datetime(Not_Null)
     */
    public $finishTime;

    /**
     * @Orm\Field\Datetime(Not_Null)
     */
    public $expirationTime;

    /**
     * @Orm\Field\Int(Size=11, Not_Null)
     */
    public $User__id;

    /**
     * @Orm\Field\Varchar(Size=128, Not_Null)
     */
    public $createIp;

    /**
     * @Orm\Field\Varchar(Size=128, Not_Null)
     */
    public $finishIp;

    /**
     * Параметры в json
     *
     * @Orm\Field\Text(Not_Null)
     */
    public $paramsJson;

    /**
	 * Успешное окончание активации
	 *
     * @return Activation Эта активация.
	 */
	public function activate()
	{
        $helperDate = $this->getService('helperDate');
		$this->update(array(
			'finished'		=> 1,
			'finishTime'	=> $helperDate->toUnix(),
			'finishIp'		=> $this->getService('request')->ip()
		));
		return $this;
	}

    /**
     * Находит активацию по коду, при условии, что она не истекла по времени.
     *
     * @param string $code Код активации.
     * @param string $type
     * @return Activation|null Найденная активация.
     */
	public function byCode($code, $type = '')
	{
        $helperDate = $this->getService('helperDate');
        $activationQuery = $this->getService('query')
            ->select('Activation.id')
            ->from('Activation')
            ->where('type', $type)
            ->where('code', $code)
            ->where('expirationTime<?', $helperDate->toUnix());
		$actionvationId = $this->getService('dds')->execute($activationQuery)
            ->getResult()->asValue();
        $actionvation = $this->getService('modelManager')->byKey(
            'Activation', $actionvationId
        );
        return $actionvation;
	}

	/**
	 * Создает и возвращает новую активацию.
	 *
     * @param array $params Параметры активации.
	 * $params ['address'] Адрес для отправки сообщения.
	 * $params ['type'] [optional] Тип (smsauth/review и т.п.)
	 * $params ['code'] Код активации.
	 * $params ['User__id'] [optional] Если не передан, id текущего.
	 * $params ['expirationTime'] Время, когда активация станет
	 * недействительной (в формате UNIX).
	 * $params ['callbackMessage'] Сообщение, попадающие в Message_Queue
	 * 	после успешной активации.
	 * @return Activation Созданная активация.
	 */
	public function create(array $params)
	{
        $helperDate = $this->getService('helperDate');
        $params['createTime'] = $helperDate->toUnix();
        $params['finishTime'] = self::EMPTY_FINISH_TIME;
        if (!isset($params['expirationTime'])) {
            $params['expirationTime'] = '2040-01-01 00:00:00';
        }
        if (!isset($params['User__id'])) {
            $params['User__id'] = $this->getService('user')->getCurrent()->key();
        }
        $params['createIp'] = $this->getService('request')->ip();
        $params['day'] = $helperDate->eraDayNum();
        if (isset($params['params']) && $params['params']) {
            $params['paramsJson'] = urlencode(json_encode($params['params']));
        }
		$activation = new Activation($params);
		return $activation->save();
	}

    /**
     * Отправить сообщение связанное с данной активации
     */
    public function sendMessage()
    {
        $mailMessageId = $this['Mail_Message__id'];
        if (!$mailMessageId) {
            return false;
        }
        $modelManager = $this->getService('modelManager');
        $mailMessage = $modelManager->bykey('Mail_Message', $mailMessageId);
        return $mailMessage->send();
    }
}