<?php
/**
 * Экранирование данных через Data_Source
 *
 * @author neon
 */
class Filter_Escape
{
	/**
	 * @inheritdoc
	 * @param string $data
	 * @return string
	 */
	public function filter($data)
	{
		$locator = IcEngine::serviceLocator();
		$dds = $locator->getService('dds');
		return $dds->escape($data);
	}
}