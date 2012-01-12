<?php

namespace Ice;

/**
 *
 * @deprecated Следует использовать "::Limit"
 *
 */

class Comment_Option_Range extends Model_Option
{
	/**
	 *
	 */
	public function before ()
	{
		$st = !empty ($this->params ['st'])
			? $this->params ['st']
			: 0;

		$count = !empty ($this->params ['count'])
			? $this->params ['count']
			: 0;

		$this->query
			->limit ($count, $st);
	}
}