<?php

/**
 * 
 * @param array $params
 * @param Smarty $smarty
 * @return string
 */
function smarty_function_Helper (array $params, Smarty $smarty)
{
    $helper = $params ['call'];
    Loader::load ('View_Helper_Manager');
    return View_Helper_Manager::get ($helper, $params);
}