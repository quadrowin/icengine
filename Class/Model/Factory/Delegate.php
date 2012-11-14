<?php

/**
 * Представитель модели
 *
 * @author goorus, morph
 */
class Model_Factory_Delegate extends Model
{
    /*
	 * Фабрика
     *
	 * @var Model_Factory
	 */
	protected $modelFactory;

	/**
	 * Конструктор
     *
	 * @param array $fields
	 * @param Model $model
	 */
	public function __construct(array $fields = array(), $model = null)
	{
		// Находим фабрику
		$this->modelFactory = Model_Manager_Delegee_Factory::factory($this);
		parent::__construct($fields, $model);
	}

	/**
	 * (non-PHPdoc)
	 * @see Model::modelName()
	 */
	public function modelName ()
	{
		return get_class($this->modelFactory);
	}

	/**
	 * @desc Задает фабрику.
	 * @param Model_Factory $factory Экземпляр фабрики.
	 */
	public function setModelFactory(Model_Factory $factory)
	{
		$this->modelFactory = $factory;
	}

	/**
	 * (non-PHPdoc)
	 * @see Model::table()
	 */
	public function table()
	{
		return $this->modelFactory->table();
	}

}