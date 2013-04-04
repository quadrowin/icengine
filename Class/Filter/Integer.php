<?php
/**
 * Стандартный фильтр чисел.
 *
 * @author Юрий, neon
 */
class Filter_Integer extends Filter_Abstract
{
	/**
	 * @inheritdoc
	 * @param string $data
	 * @return int
	 */
	public function filter($data)
	{
		$locator = IcEngine::serviceLocator();
		$helperString = $locator->getService('helperString');
		return $helperString->str2int(trim($data));
	}
}