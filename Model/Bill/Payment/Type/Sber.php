<?php

/**
 * 
 * @desc Платеж через сбербанк
 * @package IcEngine
 * @author dp
 *
 */
Loader::load('Bill_Payment_Type_Abstract');

class Bill_Payment_Type_Sber extends Bill_Payment_Type_Abstract
{

	/**
	 * (non-PHPdoc)
	 * @see Bill_Payment_Type_Abstract::assemble()
	 */
	public function assemble($bill_id = null, $sum = 0)
	{
		$result = 0;

		$user = User::getCurrent();
		$bill = Model_Manager::byKey('Bill', (int) $bill_id);



		if ($user && $user->hasRole('admin') && $bill)
		{
			if (!$sum)
			{
				$sum = $bill->totalCost;
			}

			Loader::load('Helper_Date');
			$payment = $this->instantPayment(
					array(
						'value' => $sum,
						'balance' => $sum,
						'transactionNo' => '000' . time(),
						'details' => 'Платеж подтвердил пользователь с id #' . $user->key(),
						'Bill__id' => $bill->key()
					)
			);

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
