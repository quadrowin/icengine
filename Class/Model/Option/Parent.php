<?php

/**
 * По родителю
 *
 * @author morph
 */
class Model_Option_Parent extends Model_Option
{
	/**
	 * @inheritdoc
	 */
	public function before()
	{
		$this->query->where($this->collection->modelName () . '.parentId',
			$this->params['id']);
	}
}