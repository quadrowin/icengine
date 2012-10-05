<?php

/**
 * По полю table
 *
 * @author morph
 */
class Model_Option_Table extends Model_Option
{
	/**
	 * @inheritdoc
	 */
	public function before()
	{
		$this->query->where($this->collection->modelName () . '.table',
			$this->params['table']);
	}
}