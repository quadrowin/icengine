<?php

/**
 * Description of Attribute
 *
 * @author markov
 */
class Controller_Admin_Attribute extends Controller_Abstract
{
	public function index($table, $rowId)
	{
		$modelManager = $this->getService('modelManager');
		$model = $modelManager->byKey($table, $rowId);
		if (!$model) {
			return;
		}
		$collectionManager = $this->getService('collectionManager');
		$query = $this->getService('query');
		$attribute_collection = $collectionManager->byQuery(
			'Attribute',
			$query->where('table', $model->modelName())
				->where('rowId', $model->key())
		);
		foreach ($attribute_collection as &$attribute) {
			$attribute->value = json_decode($attribute->value);
		}
		$this->_output->send(array(
			'attribute_collection'	=> $attribute_collection,
			'model'			=> $model
		));
	}

	/**
	 *
	 * @param type $table название модели
	 * @param type $rowId айдишник модели
	 * @param type $key название атрибута
	 * @param type $value значение атрибута
	 * @return type
	 */
	public function add($table, $rowId, $key, $value)
	{
		$this->_task->setTemplate(null);
		$modelManager = $this->getService('modelManager');
		$model = $modelManager->byKey($table, $rowId);
		if (!$model) {
			return;
		}
		$model->setAttribute($key, $value);
		$this->_output->send(array(
			'data'		=> array(
				'status'	=> 'ok'
			)
		));
	}
}
