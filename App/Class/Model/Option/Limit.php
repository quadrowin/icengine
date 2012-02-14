<?php

namespace Ice;

/**
 *
 * @desc Задание ограничений на выбор.
 * @author Yury Shvedov
 * @package Ice
 *
 * @param integer $count
 * @param integer $offset
 *
 */
class Model_Option_Limit extends Model_Option
{

	public function before ()
	{
		$this->query->limit (
			isset ($this->params ['count']) ? $this->params ['count'] : null,
			isset ($this->params ['offset']) ? $this->params ['offset'] : null
		);
	}

}
