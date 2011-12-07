<?php

namespace Ice;

/**
 *
 * @desc Для выбора тегов по представлению в транслите.
 * @author Yury Shvedov
 * @package Ice
 *
 */
class Component_Tag_Collection_Option_Translit extends Model_Collection_Option_Abstract
{

	/**
	 * @see Model_Collection_Option_Abstract::before ()
	 */
	public function before (Model_Collection $collection,
		Query $query, array $params)
	{
		$query
			->where ('translit', $params ['translit']);
	}

}