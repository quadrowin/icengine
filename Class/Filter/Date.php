<?php
/**
 * Фильтр получения даты
 *
 * @author Юрий, neon
 */
class Filter_Date
{
	/**
	 * Получение даты в UNIX формате YYYY-MM-DD hh:mm:ss.
	 *
	 * @param string $data
	 * @return string
	 */
	public function filter($data)
	{
		$locator = IcEngine::serviceLocator();
		$helperDate = $locator->getService('helperDate');
		return $helperDate->toUnix($helperDate->strToTimestamp($data));
	}

	/**
	 *
	 * @param string $field
	 * @param stdClass $data
	 * @param stdClass|Objective $scheme
	 * @return mixed
	 */
	public function filterEx($field, $data, $scheme)
	{
		$locator = IcEngine::serviceLocator();
		$helperDate = $locator->getService('helperDate');
		$timestamp =
			(isset($scheme->input) && $scheme->input == 'php') ?
			strtotime($data->$field) :
			$helperDate->strToTimestamp($data->$field);
		if (isset($scheme->output) && $scheme->output == 'timestamp') {
			return $timestamp;
		}
		return $helperDate->toUnix($timestamp);
	}
}