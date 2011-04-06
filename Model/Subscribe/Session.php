<?php

class Subscribe_Session extends Model
{
	/**
	 * @desc Получить последнюю сессию подписки
	 * @param Model $subscibe
	 * @return array	
	 */
	public static function lastFor (Model $subscribe)
	{
		Loader::load ('Helper_Process');
		return DDS::execute (
			Query::instance ()
			->select ('*')
			->from ('Subscribe_Session')
			->where ('Subscribe__id', $subscribe->key ())
			->where ('status', Helper_Process::SUCCESS)
			->order ('finishDate DESC')
			->limit (1)
		)
			->getResult ()
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