<?php

/**
 * Хелпер для Controller_Schedule
 * 
 * @author morph
 * @Service("helperSchedule")
 */
class Helper_Schedule
{
    /**
     * Получить сформированную строку аргументов
     * 
     * @param array $params
     * @return array
     */
    public function get($params)
    {
        $output = '';
        $param = json_decode(urldecode($params), true);
        foreach ($params as $param => $value) {
            if (is_numeric($param)) {
                $param = $value;
                $value = null;
            } elseif ($param == $value) {
                $value = null;
            }
            $output .= ' --' . $param . ($value ? 
                (is_numeric($value) ? $value : '"' . $value . '"') : ''
            );
        }
        return $output;
    }
}