<?php

/**
 * По полю show
 *
 * @author morph
 */
class Model_Option_Show extends Model_Option
{
	/**
	 * @inheritdoc
	 */
	public function before()
	{
		$this->query->where($this->collection->modelName() . '.show', 1);
	}
}