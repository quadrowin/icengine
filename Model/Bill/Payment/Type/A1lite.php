<?php
/**
 * 
 * @desc Платеж по A1Lite
 * @author Гурус
 * @package IcEngine
 *
 */

Loader::load ('Bill_Payment_Type_Abstract');

class Bill_Payment_Type_A1lite extends Bill_Payment_Type_Abstract
{
	
	/**
	 * (non-PHPdoc)
	 * @see Bill_Payment_Type_Abstract::assemble()
	 */
	public function assemble ()
	{
		$result = 0;
		
		$path = IcEngine::root () . 'a1lite/success_new/';
		$files = scandir ($path);
		
		foreach ($files as $file)
		{
			if ($file == '.' || $file == '..')
			{
				continue;
			}
			
			$f = fopen ($path . $file, "r+");
			if (!$f)
			{
				continue;
			}
			
			if (!flock ($f, LOCK_EX | LOCK_NB))
			{
				fclose ($f);
				continue;
			}
			
			$log_message = fread ($f, filesize ($path . $file));
			if ($log_message)
			{
				$message = json_decode ($log_message, true);
				if ($message)
				{
					
					$payment = $this->instantPayment (array (
						'value'			=> $message ['system_income'],
						'transactionNo'		=> $message ['tid'],
						'details'		=> $log_message,
						'Bill__id'		=> (int) $message ['order_id']
					));

					if ($payment)
					{	
						fseek ($f, 0, SEEK_SET);
						ftruncate ($f, 0);
					
						$payment->update (array (
							'endProcessTime' => Helper_Date::toUnix ()
						));
						++$result;
					}
				}
			}
			
			flock ($f, LOCK_UN);
			fclose ($f);
			echo $path . $file . PHP_EOL;
			unlink ($path . $file);
		}
		
		return $result;
	}
	
}
