<?php

namespace Ice;

class Comment_Option_User extends Model_Option
{
	/**
	 *
	 */
	public function before ()
	{
		$this->query
			->leftJoin (
				'User',
				'Comment.User__id=User.id'
			);
	}

	/**
	 *
	 */
	public function after ()
	{
		$attrs = !empty ($this->params ['attrs'])
			? array_values ((array) $this->params ['attrs'])
			: array ();

		$avatar = !empty ($this->params ['avatar'])
			? (bool) $this->params ['avatar']
			: false;

		foreach ($this->collection as $item)
		{
			if ($avatar)
			{
				$ava = $item
					->User
						->component ('Avatar')
							->first ();

			    $item->data ('avatar', $ava);
			}
			for ($i = 0, $icount = count ($attrs); $i < $icount; ++$i)
			{
				$item
					->User
						->attr ($attrs [$i]);
			}
		}
	}
}