<?php

class Subscribe_Session extends Model
{
	/**
	 * @desc Получить последнюю сессию подписки
	 * @param integer $subscibe_id
	 * @return array	
	 */
	public static function lastFor ($subscribe_id)
	{
		return DDS::execute (
			Query::instance ()
			->from ('Subscribe_Session')
			->where ('Subscribe__id', $subscribe_id)
			->where ('statuc', Helper_Process::SUCCESS)
			->order ('finishDate DESC')
			->limit (1)
		)
			->asRow ();
	}
	
	/**
	 * @desc Изменить статус сессии
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