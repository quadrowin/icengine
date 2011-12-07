<?php

namespace Ice;

/**
 *
 * @desc
 * @author Юрий Шведов
 * @package Ice
 *
 */
class Controller_Option_Action extends Model_Option
{

	public function before ()
	{
		$this->query->where ('action', $this->params ['is']);
	}

}
