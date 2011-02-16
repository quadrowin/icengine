<?php
/**
 * @author Юрий
 * @package IcEngine
 */


/**
 * 
 * @param integer $index
 * 		Номер месяца от 1 до 12
 * @param integer $form
 * 		Форма
 * @return string
 */
function smarty_modifier_month_name ($index, $form = 1)
{
	return Helper_Date::monthName ($index, $form);
}