<?php

/**
 * Абстрактный атрибут валидации модели
 * 
 * @author morph
 */
abstract class Model_Validator_Attribute_Abstract
{
    /**
     * Поле
     * 
     * @var string
     */
    protected $field;
    
    /**
     * Транспорт данных
     * 
     * @var Data_Transport|array
     */
    protected $input;
    
    /**
     * Модель
     * 
     * @var Model
     */
    protected $model;
    
    /**
     * Значение
     * 
     * @var mixed
     */
    protected $value;
    
     /**
     * Валидировать атрибут
     * 
     * @return boolean
     */
	abstract public static function doValidate();
    
    /**
     * Начать вадилацию данных
     * 
     * @param Model $model
     * @param string $field
     * @param mixed $value
     * @param Data_Transport $input
     * @return boolean
     */
    public function execute($model, $field, $value, $input)
    {
        $this->model = $model;
        $this->field = $field;
        $this->value = $value;
        $this->input = $input;
        $this->doValidate();
    }
    
    /**
     * Получить значение поля
     * 
     * @return string 
     */
    public function getField()
    {
        return $this->field;
    }

    /**
     * Получить значение входящего транспорта
     * 
     * @return Data_Transport|array
     */
    public function getInput()
    {
        return $this->input;
    }
    
    /**
     * Получить модель
     * 
     * @return Model
     */
    public function getModel()
    {
        return $this->model;
    }
    
    /**
     * Получить текущее значение поля из транспорта
     * 
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }
    
    /**
     * Изменить значение поля
     * 
     * @param string $field
     */
    public function setField($field)
    {
        $this->field = $field;
    }
    
    /**
     * Изменить значение входящего транспорта
     * 
     * @param Data_Transport|array $input
     */
    public function setInput($input)
    {
        $this->input = $input;
    }
    
    /**
     * Изменить модель
     * 
     * @param Model $model
     */
    public function setModel($model)
    {
        $this->model = $model;
    }
    
    /**
     * Изменить значение с транспорта
     * 
     * @param mixed $value
     */
    public function setValue($value)
    {
        $this->value = $value;
    }
}