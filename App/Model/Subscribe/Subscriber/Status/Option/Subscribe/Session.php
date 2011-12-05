<?php

namespace Ice;

/**
 *
 * @desc Опция для выбора статусов по сессии рассылки
 * @author Юрий Шведов
 * @package Ice
 *
 */
class Subscribe_Subscriber_Status_Option_Subscribe_Session extends Model_Option
{

	public function before ()
	{
		$this->query->where ('Subscribe_Session__id', $this->params ['id']);
	}

}
