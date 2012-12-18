<?php

/**
 * Опшен для получения юзера по паролю
 *
 * @author neon
 */
class User_Option_Password extends Model_Option
{
	/**
	 * @inheritdoc
	 */
	public function before()
	{
		$locator = IcEngine::serviceLocator();
		$cryptManager = $locator->getService('cryptManager');
		if ($this->params['value']) {
			$password = $this->params['value'];
			if (isset($this->params['type']) &&
					$this->params['type'] == 'RSA') {
				$rsa = $cryptManager->get('RSAW2');
				$password = $rsa->encode($this->params['value']);
			} else {
				$password = md5($password);
			}
			$this->query
				->where('password', $password);
		}
	}
}