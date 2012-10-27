<?php

/**
 * Опшин для полечения сущности по полю name
 *
 * @author morph
 */
class Model_Option_Name extends Model_Option
{
	/**
	 * @inheritdoc
	 */
	public function before()
	{
		$this->query->where($this->collection->modelName () . '.name',
			$this->params['value']);
	}
}