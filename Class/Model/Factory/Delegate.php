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