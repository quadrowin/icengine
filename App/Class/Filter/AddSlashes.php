<?php

namespace Ice;

/**
 *
 * @desc Экранирование добавление слэшей
 * @author Morph
 *
 */
class Filter_AddSlashes
{

	public function filter ($data)
	{
		return addslashes ($data);
	}

}