<?php

/**
 * Сущность схемы связей моделей
 * 
 * @author morph
 */
class Model_Mapper_Scheme_State
{
	/**
	 * Имя класса
	 * 
     * @var string
	 */
	private $class;

	/**
	 * Имя сущности
	 * 
     * @var string
	 */
	private $name;

	/**
	 * Значение сущности
	 * 
     * @var mixed
	 */
	private $value;

	/**
     * Конструктор
     * 
	 * @param string $class Имя класса
	 * @param string $name Название
	 * @param mixed $value  Значение
	 */
	public function __construct($class, $name, $value)
	{
		$this->class = $class;
		$this->name = $name;
		$this->value = $value;
	}

	/**
	 * Получить класс сущности
	 * 
     * @return string
	 */
	public function getClass()
	{
		return $this->class;
	}

	/**
	 * Получить имя сущности
	 * 
     * @return string
	 */
	public function getName()
	{
		return $this->name;
	}

	/**
	 * Получить значение сущности
	 * 
     * @return mixed
	 */
	public function getValue()
	{
		return $this->value;
	}

	/**
	 * Изменить имя класса
	 * 
     * @param string $class
	 */
	public function setClass($class)
	{
		$this->class = $class;
	}

	/**
	 * Изменить имя сущности
	 * 
     * @param string $name
	 */
	public function setName($name)
	{
		$this->name = $name;
	}

	/**
	 * Изменить значение сущности
	 * 
     * @param mixed $value
	 */
	public function setValue($value)
	{
		$this->value = $value;
	}
}