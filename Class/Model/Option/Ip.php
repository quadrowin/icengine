<?php

/**
 * По полю ip
 *
 * @author morph
 */
class Model_Option_Ip extends Model_Option
{
	/**
	 * @inheritdoc
	 */
	public function before()
	{
		$this->query->where($this->collection->modelName () . '.ip',
			$this->params['ip']);
	}
}