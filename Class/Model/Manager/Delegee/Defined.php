<?php

/**
 * Класс для создания определенных моделей
 * 
 * @author morph, goorus
 */
class Model_Manager_Delegee_Defined
{
	/**
	 * Создает предопределенную модель
     * 
	 * @param string $model Название модели
	 * @param string $key Ключ (id)
	 * @param Model|array $object Объект или данные
	 * @return Model В случае успеха объект, иначе null.
	 */
	public static function get($modelName, $key, $object)
	{
		$rows = $modelName::$rows;
        $params = is_array($object) ? $object : array();
		foreach ($rows as $row){
			if ($row['id'] != $key) {
                continue;
            }
            if (!isset($row['name'])) {
                continue;
            }
            $delegeeName = $modelName . '_' . $row['name'];
            if (Loader::tryLoad($delegeeName)) {
                $modelName = $delegeeName;
            }
            break;
		}
        $model = new $modelName($params);
        return $model;
	}
}