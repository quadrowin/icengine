<?php

namespace Ice;

class Component_Comment_Collection_Option_User extends Model_Collection_Option_Abstract
{
	/**
	 *
	 * @param Model_Collection $items
	 * @param Query $query
	 * @param array $params
	 */
	public function before (Model_Collection $items, Query $query, array $params)
	{
		$query
			->leftJoin (
				'User',
				'Component_Comment.User__id=User.id'
			);
	}

	/**
	 *
	 * @param Model_Collection $items
	 * @param Query $query
	 * @param array $params
	 */
	public function after (Model_Collection $items, Query $query, array $params)
	{
		$attrs = !empty ($params ['attrs']) ?
			array_values ((array) $params ['attrs']) :
			array ();

		$avatar = !empty ($params ['avatar']) ?
			(bool) $params ['avatar'] :
			false;

		foreach ($items as $item)
		{
			if ($avatar)
			{
				$ava = $item
					->User
						->component ('Avatar')
							->first ();

			    $item->data ('avatar', $ava);
			}
			for ($i = 0, $icount = count ($attrs); $i < $icount; $i++)
			{
				$item
					->User
						->attr ($attrs [$i]);
			}
		}
	}
}