<?php

namespace Ice;

class Comment_Option_Link extends Model_Option
{

	/**
	 *
	 */
	public function before ()
	{
		$this->query
			->where ('table', $this->params ['table'])
			->where ('rowId', $this->params ['rowId']);
	}

}