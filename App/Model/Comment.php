<?php

namespace Ice;

/**
 *
 * @desc Комментарий
 * @author Юрий Шведов
 * @package Ice
 *
 */
class Comment extends Model_Child
{

	/**
	 * @desc Возвращает родительский комментарий.
	 * @return Comment
	 */
	public function getParent ()
	{
		return $this->_getModelManager ()
			->get ($this->modelName (), $this->parentId);
	}

	/**
	 * @return string
	 */
	public function text ()
	{
		return htmlspecialchars_decode (trim (stripslashes ($this->text)));
	}

	/**
	 * @desc Возвращает уровень комментария относительно корня.
	 * @param integer $rate
	 * 		Множитель. Результат будет домножен на указанную величину.
	 * @return integer
	 */
	public function level ($rate = 1)
	{
	    if ($this->parentId)
	    {
	        return ($this->getParent ()->level () + 1) * $rate;
	    }
	    else
	    {
	        return 0;
	    }
	}

}