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
	public function assemble($sum = 0, $user_id = 0)
	{
		$result = 0;

		$user = User::getCurrent();

		if ($user_id && (($user && $user->hasRole('admin')) || User::id ()==-1) && !($sum < 1 || preg_match("/[^0-9]/", $sum)))
		{
			Loader::load('Helper_Date');
			Loader::load('Bill_Payment');
			$payment = new Bill_Payment(array(
						'Bill_Payment_Type__id' => $this->key(),
						'wallet' => '',
						'value' => $sum,
						'balance' => $sum,
						'transactionNo' => '',
						'details' => 'Корректировка баланса была произведена администратором #' . $user->key(),
						'Bill__id' => 0,
						'beginProcessTime' => Helper_Date::toUnix(),
						'endProcessTime' => '2000-01-01 00:00:00',
						'User__id' => $user_id,
						'Discount_Payment_Amount__id' => 1,
						'balance_time_update' => Helper_Date::toUnix()
					));

			$payment->save();

			if ($payment)
			{
				$client_user = Model_Manager::byKey('User', (int) $user_id);
				$client_user->getBalance()->change($payment->value, 'Корректировка баланса была произведена администратором #' . $user->key(), $client_user);
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
