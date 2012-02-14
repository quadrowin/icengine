<?php

namespace Ice;

/**
 *
 * @desc Опшн для выбора раздела по адресу.
 * @author Юрий Шведов
 * @package Ice
 *
 */
class Content_Category_Option_Url extends Model_Option
{

	public function before ()
	{
		$this->query->where ('url', $this->params ['url']);
	}

}
