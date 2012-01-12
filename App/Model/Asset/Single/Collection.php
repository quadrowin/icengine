<?php

namespace Ice;

Loader::load ('Asset_Collection');

/**
 *
 * @desc Абстрактная коллекция для одиночного компонента
 * @author Юрий Шведов
 * @package Ice
 *
 */
abstract class Asset_Single_Collection extends Asset_Collection
{

	/**
	 * @desc Создаение компонента, если его не существует
	 * @return Model
	 */
	protected function _createSingle ()
	{
		$asset_name = $this->modelName ();
		$asset = new $asset_name (array (
			'table' => $this->_model->modelName (),
			'rowId' => $this->_model->key ()
		));
		return $asset->save ();
	}

	/**
	 * (non-PHPdoc)
	 * @see Model_Collection::load()
	 */
	public function load ($columns = array ())
	{
		parent::load ($columns);

		if (!$this->_items)
		{
			$this->_items = array (
				$this->_createSingle ()
			);
		}

		return $this;
	}

}