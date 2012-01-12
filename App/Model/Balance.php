<?php

namespace Ice;

/**
 *
 * @desc Модель баланса.
 * @author Yury Shvedov
 * @package Ice
 *
 */
class Balance extends Model
{

	/**
	 * @desc Получение баланса для модели.
	 * @param Model $model Модель.
	 * @return Balance Баланс
	 */
	public static function getFor ($model)
	{
		return $model->component ('Balance', 0);
	}

	/**
	 * @desc Изменяет баланс модели.
	 * @param Model $model Модель.
	 * @param float $change Изменение баланса.
	 * @param string $comment Комментарий.
	 * @return Balance Баланс модели.
	 */
	public static function changeFor ($model, $change, $comment = '')
	{
		$balance = self::getFor ($model);

		$balance->change ($change, $comment);

		return $balance;
	}

	/**
	 * @desc Изменяет баланс модели
	 * @param float $change
	 * @param string $comment [optional]
	 * @return Balance_Log
	 */
	public function change ($change, $comment = '')
	{
		Loader::load ('Balance_Log');
		$log = Balance_Log::addLog (
			$this->table,
			$this->rowId,
			$this->value,
			$change,
			$comment
		);

		$this->update (array (
			'value'	=> $this->value + $change
		));

		return $log;
	}

}