<?php

/**
 * Экранирование добавление слэшей
 *
 * @author morph
 */
class Data_Filter_AddSlashes extends Data_Filter_Abstract
{
    /**
     * @inheritdoc
     */
	public function filter($data)
	{
		return addslashes($data);
	}
}