<?php
/**
 *
 * @desc
 * @param string $string
 * @return string
 *
 */
function smarty_modifier_to_normal_date ($string)
{
	Ice\Loader::load ('Helper_Date');
	return Ice\Helper_Date::toCasualDate ($string);
}