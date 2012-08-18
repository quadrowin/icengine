<?php

class Component_Comment_Option_Link extends Model_Option
{

	/**
	 *
	 * @param Model_Collection $items
	 * @param Query $query
	 * @param array $params
	 */
	public function before ()
	{
		$this->query
			->where ('table', $this->params ['table'])
			->where ('rowId', $this->params ['rowId']);
	}

}