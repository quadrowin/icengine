<?php

class Subscribe_Subscriber_Status extends Model
{
	/**
	 * @desc Получить статусы для сессия
	 * @param integer $session_id
	 * @param integer $status
	 * @return Model_Collection
	 */
	public function forSession ($session_id, $status, $limit = null)
	{
		return new Model_Collection (
			DDS::execute (
				Query::instance ()
				->from ('Subscribe_Session_Status')
				->where ('status', $status)
				->where ('Subscribe_Session__id', $session_id)
				->limit ($limit)
			)
				->asTable ()
		);
	}
	
	/**
	 * @desc Изменить статус
	 * @param integer $status
	 * @return Model
	 */
	public static function setStatus ($status)
	{
		$this->status = $status;
		$this->save ();
		return $this;
	}
}