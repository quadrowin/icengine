<?php

/**
 * Хелпер для работы с сервисами
 * 
 * @author morph
 * @Service("helperString")
 */
class Helper_Service
{
    /**
     * Привести имя, написаное вида className в Class_Name
     * 
     * @param string $name
     * @return string
     */
    public function normalizeName($name)
    {
        $matches = array();
		$reg_exp = '#([A-Z]*[a-z]+)#';
		preg_match_all($reg_exp, $name, $matches);
		if (empty($matches[1][0])) {
			return $name;
		}
		return implode('_', array_map('ucfirst', $matches[1]));
    }
}