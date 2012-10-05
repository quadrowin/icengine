<?php

/**
 * Модель
 *
 * @author morph
 */
class Model_Option_Model extends Model_Option
{
	/**
	 * @inheritdoc
	 */
	public function before()
	{
		$this->query->where(
			$this->collection->modelName() . '.model',
			$this->params['model']
		);
	}
}