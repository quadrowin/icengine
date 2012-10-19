<?php

/**
 * Опшин для полечения сущности по полю url
 *
 * @author morph
 */
class Model_Option_Url extends Model_Option
{
	/**
	 * @inheritdoc
	 */
	public function before()
	{
		$this->query->where($this->collection->modelName () . '.url',
			$this->params['url']);
	}
}