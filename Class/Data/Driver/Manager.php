<?php

/**
 * Менеджер драйверов для источников данных
 * 
 * @author goorus, morph
 * @Service("dataDriverManager")
 */
class Data_Driver_Manager extends Manager_Abstract
{
	/**
	 * Уже созданные драйверы
     * 
	 * @var array
	 */
	protected $drivers = array();

	/**
	 * Получить драйвер по имени
     * 
	 * @param string $name
     * @param array $params
	 * @return Data_Driver_Abstract
	 */
	public function get($name, $params = array())
	{
		if (!isset($this->drivers[$name])) {
			$className = 'Data_Driver_' . $name;
			$this->drivers[$name] = new $className;
		}
        if ($params) {
            foreach ($params as $key => $value) {
                $this->drivers[$name]->setOption($key, $value);
            }
        }
		return $this->drivers[$name];
	}
}