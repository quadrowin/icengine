<?php

/**
 * Экранирование добавление слэшей
 *
 * @author morph
 */
class Filter_AddSlashes
{
    /**
     * @inheritdoc
     */
	public function filter($data)
	{
		return addslashes($data);
	}
}