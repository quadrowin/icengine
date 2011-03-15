<?php
/**
 * 
 * @desc Контроллер платежей
 * @author Гурус
 * @package IcEngine
 *
 */
class Controller_Payment extends Controller_Abstract
{
	
	/**
	 * @desc Собрать новые смс и платежи 
	 * (такие как A1Lite, A1Sms)
	 */
	public function assemble ()
	{
		Loader::load ('Bill_Payment_Type_Collection');
		$types = new Bill_Payment_Type_Collection ();
		foreach ($types as $type)
		{
			$count = $type->assemble ();
			$type->data ('count', $count);
		}
		$this->_output->send (array (
			'types'	=> $types
		));
	}
	
}