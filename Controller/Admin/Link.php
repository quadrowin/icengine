<?php

/**
 * Контроллер админки линковки
 *
 * @package IcEngine
 * @author Илья Колесников
 * @copyright i-complex.ru
 */
class Controller_Admin_Link extends Controller_Abstract
{
	public function index()
	{
		$userService = $this->getService('user');
		if (!$userService->getCurrent()->isAdmin()) {
			return $this->replaceAction(
				'Error',
				'accessDenied'
			);
		}
		$helperDataSource = $this->getService('helperDataSource');
		$tables = $helperDataSource->tables();
		$result = array();
		foreach ($tables as $table) {
			$result[] = array(
				'name'	=> $table->Name,
				'title'	=> $table->Comment
			);
		}
		$this->output->send(array(
			'tables'	=> $result
		));
	}

	/**
	 * Получаем модели коллекции
	 */
	public function items()
	{
		$table = $this->input->receive('table');
		$modelScheme = $this->getService('modelScheme');
		$class_name = $modelScheme->tableToModel($table);
		$result = array();
		$collectionManager = $this->getService('collectionManager');
		$collection = $collectionManager->create($class_name);
		foreach ($collection as $model) {
			$result[] = array(
				'id'	=> $model->key(),
				'name'	=> $model->title()
			);
		}
		$this->_task->setTemplate(null);
		$this->output->send(array(
			'data'	=> array(
				'items'	=> $result
			)
		));
	}

	/**
	 * Получаем модели, в том числе прилинкованные
	 */
	public function linkRoll()
	{
		list(
			$table1,
			$table2,
			$row1
		) = $this->_input->receive(
			'table1',
			'table2',
			'row1'
		);
		$modelScheme = $this->getService('modelScheme');
		$class_name1 = $modelScheme->tableToModel($table1);
		$class_name2 = $modelScheme->tableToModel($table2);
		$modelManager = $this->getService('modelManager');
		$model1 = $modelManager->get(
			$class_name1,
			$row1
		);
		$collectionManager = $this->getService('collectionManager');
		$model2_collection = $collectionManager->create($class_name2);
		$helperLink = $this->getService('helperLink');
		$linked_models = $helperLink->linkedItems(
			$model1,
			$class_name2
		);
		$result = array();
		foreach ($model2_collection as $i => $model) {
			$result[$i] = array(
				'id'		=> $model->key(),
				'name'		=> $model->title(),
				'linked'	=> 0
			);
			$filtered = $linked_models->filter(array(
				$modelScheme->keyField($class_name2)	=> $model->key()
			));
			if ($filtered->count()) {
				$result[$i]['linked'] = 1;
			}
		}
		$this->_task->setTemplate(null);
		$this->output->send(array(
			'data'	=> array(
				'items'	=> $result
			)
		));
	}

	/**
	 * Сохраняем изменения
	 */
	public function save()
	{
		list(
			$table1,
			$table2,
			$row1,
			$models2
		) = $this->_input->receive(
			'table1',
			'table2',
			'row1',
			'models2'
		);
		$modelScheme = $this->getService('modelScheme');
		$class_name1 = $modelScheme->tableToModel($table1);
		$class_name2 = $modelScheme->tableToModel($table2);
		$modelManager = $this->getService('modelManager');
		$model1 = $modelManager->get(
			$class_name1,
			$row1
		);
		$helperLink = $this->getService('helperLink');
		$helperLink->unlinkWith(
			$model1,
			$class_name2
		);
		if (!$models2) {
			return;
		}
		$modelManager = $this->getService('modelManager');
		foreach ($models2 as $model2_id){
			$model2 = $modelManager->byKey(
				$class_name2,
				$model2_id
			);
			if ($model2) {
				$helperLink->link(
					$model1,
					$model2
				);
			}
		}
		$this->_task->setTemplate(null);
	}

	/**
	 * Получаем список таблиц
	 */
	public function tables()
	{
		$helperDataSource = $this->getService('helperDataSource');
		$tables = $helperDataSource->tables();
		$result = array();
		foreach ($tables as $table) {
			$result[] = array(
				'name'	=> $table->Name,
				'title'	=> $table->Comment
			);
		}
		$this->_task->setTemplate(null);
		$this->output->send(array(
			'data'	=> array(
				'tables'	=> $result
			)
		));
	}
}