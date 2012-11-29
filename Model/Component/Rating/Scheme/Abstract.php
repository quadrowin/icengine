<?php
/**
 * Базовая схема рейтинга.
 *
 * @author Юрий
 * @package IcEngine
 */
class Component_Rating_Scheme_Abstract extends Model_Factory_Delegate
{
	/**
	 * Изменение рейтинга
	 *
	 * @param string $table Модель
	 * @param integer $row_id Запись
	 * @param integer $value Изменение рейтинга.
	 * Может быть величиной изменения или типом, в зависимости от схемы.
	 * @return Component_Rating
	 */
	public function vote ($table, $row_id, $value)
	{
		$modelManager = $this->getService('modelManager');
		$query = $this->getService('query');
		$rating = $modelManager->byQuery(
			'Component_Rating',
			$query->where('table', $table)
				->where('rowId', $row_id)
		);
		$helperDate = $this->getService('helperDate');
		if (!$rating) {
			$rating = $modelManager->create (
				'Component_Rating',
				array(
					'table'			=> $table,
					'rowId'			=> $row_id,
					'votes'			=> 0,
					'value'			=> 0,
					'changeTime'	=> $helperDate->NULL_DATE
				)
			);
		}
		return $rating->increment($value);
	}
}