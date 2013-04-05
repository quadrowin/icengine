<?php
/**
 * Стандартный фильтр чисел.
 *
 * @author goorus, neon
 */
class Filter_Integer extends Filter_Abstract
{
	/**
	 * @inheritdoc
	 */
	public function filter($data)
	{
		$locator = IcEngine::serviceLocator();
		$helperString = $locator->getService('helperString');
		return $helperString->str2int(trim($data));
	}
}