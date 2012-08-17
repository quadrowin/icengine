<?php

/**
 * 
 * @desc Платеж - корректировка баланса
 * @package IcEngine
 * @author dp
 *
 */
Loader::load('Bill_Payment_Type_Abstract');

class Bill_Payment_Type_Correct extends Bill_Payment_Type_Abstract
{

	/**
	 * (non-PHPdoc)
	 * @see Bill_Payment_Type_Abstract::assemble()
	 */
	public function assemble($sum, $user_id)
	{
		$result = 0;

		$client_user = Model_Manager::byKey('User', (int) $user_id);

		$user = User::getCurrent();

		if ($user && $user->hasRole('admin') && $user && !($sum < 1 || preg_match("/[^0-9]/", $sum)))
		{
			Loader::load('Helper_Date');
			Loader::load('Bill_Payment');
			$payment = new Bill_Payment(array(
						'Bill_Payment_Type__id' => $this->key(),
						'wallet' => '',
						'value' => $sum,
						'balance' => $sum,
						'transactionNo' => '00000' . time(),
						'details' => 'Корректировка баланса была роизведена пользователем с id #' . $user->key(),
						'Bill__id' => 0,
						'beginProcessTime' => Helper_Date::toUnix(),
						'endProcessTime' => '2000-01-01 00:00:00',
						'User__id' => $client_user->User__id,
						'Discount_Payment_Amount__id' => $discount_amount->key(),
						'balance_time_update' => Helper_Date::toUnix()
					));

			$payment->save();

			if ($payment)
			{
				$payment->update(
						array(
							'endProcessTime' => Helper_Date::toUnix()
						)
				);
				++$result;
			}
		}
		return $result;
	}

}
