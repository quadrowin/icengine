<?php
/**
 * 
 * @desc Представитель модели.
 * @author Юрий Шведов
 * @package IcEngine
 *
 */
class Model_Factory_Delegate extends Model
{
	
	/**
	 * @desc Фабрика
	 * @var Model_Factory
	 */
	protected $_modelFactory;
	
	/**
	 * @desc 
	 * @param array $fields
	 */
	public function __construct (array $fields = array ())
	{
		// Находим фабрику
		Loader::load ('Model_Manager_Delegee_Factory');
		$this->_modelFactory = Model_Manager_Delegee_Factory::factory ($this);
		parent::__construct ($fields);
	}
	
	/**
	 * (non-PHPdoc)
	 * @see Model::modelName()
	 */
	public function modelName ()
	{
		return get_class ($this->_modelFactory);
	}
	
	/**
	 * @desc Задает фабрику.
	 * @param Model_Factory $factory Экземпляр фабрики.
	 */
	public function setModelFactory (Model_Factory $factory)
	{
		$this->_modelFactory = $factory;
	}
	
	/**
	 * (non-PHPdoc)
	 * @see Model::table()
	 */
	public function table ()
	{
		return $this->_modelFactory->table ();
	}
	
}