<?php

/**
 * Опшин для полечения сущности по полю rowId
 *
 * @author morph
 */
class Model_Option_Row extends Model_Option
{
	/**
	 * @inheritdoc
	 */
	public function before()
	{
		$this->query->where($this->collection->modelName () . '.rowId',
			$this->params['id']);
	}
}