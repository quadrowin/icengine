<?php

Loader::load("Helper_Diff");

abstract class Diff_Renderer
{
	protected function renderTemplate($field,$parent = null)
	{
		$view = View_Render_Manager::pushViewByName ('Smarty');
		try
		{
			$view->assign ('field',$field);
			$view->assign ('parent',$parent);
			$view->assign ('renderer',$this);
			return $view->fetch( $this->template() );
		}
		catch (Exception $e)
		{
			$msg =
				'[' . $e->getFile () . '@' .
				$e->getLine () . ':' .
				$e->getCode () . '] ' .
				$e->getMessage () . PHP_EOL;

			error_log (
				$msg . PHP_EOL .
				$e->getTraceAsString () . PHP_EOL,
				E_USER_ERROR, 3
			);
			Debug::log ($msg);
			$result ['error'] = 'Controller_Manager: Error in template.';
		}
		View_Render_Manager::popView ();
	}
	public function valueTemplate()
	{
		return "Controller/".str_replace("_","/",get_class($this))."/value.tpl";
	}
	public function editTemplate()
	{
		return "Controller/".str_replace("_","/",get_class($this))."/edit.tpl";
	}
	protected function template()
	{
		return "Controller/".str_replace("_","/",get_class($this));
	}
	public function render($field,$parent = null)
	{
		return $this->renderTemplate($field,$parent);
	}
	
	public function updateFromInput($model, $field, $input, $parent ='')
	{
		$action = $field->renderer->getActionFromInput($field, $input, $parent);
		switch($action)
		{
			case "skip":
				break;
			case "leave-as-now":
				Helper_Diff::deleteFieldEdits($field);
				break;
			case "set-own":
				$new_value = $field->type->getNewValueFromInput($field, $input, $parent);
				$field->type->setNewValue($model, $field, $new_value);
				Helper_Diff::deleteFieldEdits($field);
				break;
			default:
				$edit_id = (int)str_replace('change-','',$action);
				if ($edit_id>0) 
				{
					$edit = Model_Manager::byKey('Edit',$edit_id);
					$diff = Helper_Diff_Renderer::getDiff($edit);
					$new_value = $diff->{$field->name}->value;
					$field->type->setNewValue($model, $field, $new_value);
					Helper_Diff::deleteFieldEdits($field);
				}
				break;
		}
	}
	
	public function getActionFromInput($field,$input,$parent = '')
	{
		$action = $input->receive($parent.$field->name.'-edits');
		return $action;
	}
	
	public function makeInput($edit_ids,$field, $parent = '')
	{
		if (is_array($edit_ids) && count($edit_ids)>0)
			return array(
				$parent.$field->name.'-edits' => 'change-'.$edit_ids[0]
			);
	}
}

class Model_Renderer extends Diff_Renderer
{
	
	public function template()
	{
		return "Controller/Diff/Renderer/Model/".str_replace("_","/",get_class($this->model));
	}
	public function render($field,$parent = null)
	{
		$this->model = $field;
		return $this->renderTemplate($field);
	}
}

abstract class Diff_Renderer_ValueType extends Diff_Renderer
{
}

class Diff_Renderer_String extends Diff_Renderer_ValueType
{
	
}
class Diff_Renderer_List extends Diff_Renderer 
{
	private $_list;
	public function getList()
	{
		return $this->_list;
	}
	public function getListItemById($id)
	{
		foreach($this->_list as $li)
		{
			if ($li->id==$id)
			{
				return $li->name;
			}
		}
		return "";
	}
	
	public function render($field,$parent = null)
	{
		if ($field->type->config()->get_list)
		{
			$handler = $field->type->config()->get_list;
			$this->_list = Executor::execute($handler,array($field));
		} 
		else
			$this->_list = Model_Collection_Manager::create($field->type->config()->model_class);
		return parent::render($field, $parent);
	}
	
}

class Diff_Renderer_ForeignKey extends Diff_Renderer_List {
	
}

class Diff_Renderer_ManyToMany extends Diff_Renderer_List
{

}

class Diff_Renderer_OneToMany extends Diff_Renderer_List
{
	public function fieldValueIsDeleted($field,$v)
	{
		
		foreach($field->edits as $edit)
		{
			$coll = new Model_Collection();
			$coll->fromArray($edit->value->asArray());
			if (!in_array($v->id,$coll->column('id')))
			{
				return $edit;
			}
		}
	}
	
	public function render($field,$parent = null)
	{
		foreach($field->value as $v)
		{
			$v->set('childRenderer', new Helper_Diff_Renderer($v));
		}
		foreach($field->edits as $edit)
		{
			foreach($edit->value as $k => $v)
			{
				$model = Model_Manager::byKey($field->type->config()->model_class,$v);
				if (!$model)
				{
					$model = Model_Manager::create($field->type->config()->model_class,array("id" => $v));	
				}
				$model->set('childRenderer', new Helper_Diff_Renderer($model));
				$edit->value[$k] = $model;
			}			
		}
		return parent::render($field, $parent);
	}
	
	public function updateFromInput($model, $field, $input, $parent = '')
	{
		$vals = $input->receive( $field->name );
		foreach($vals as $id => $v)
		{
			if (!is_array($v))
				continue;
			$action = '';
			$subaction='skip';
			if (array_key_exists('delete-edits',$v))
			{
				$action = 'delete';
				$subaction = $v['delete-edits'];
			} 
			elseif (array_key_exists('new-edits',$v)) 
			{
				$action = 'add';
				$subaction = $v['new-edits'];
			} 
			elseif (array_key_exists('edits',$v))
			{
				$action = 'edit';				
			}
			$child_model = Model_Manager::byKey($field->type->config()->model_class, (int)$id);
			switch($action)
			{
				case "delete":
					if (!$child_model)
						break;
					switch($subaction) 
					{
						case "accept":
							$field->value = $field->value->filter(array("id!=" => $id));
							Helper_Diff::deleteModelEdits($child_model);
							Helper_Diff::deleteFieldValue($field,$id);
							$child_model->delete();
							break;
						case "cancel":
							Helper_Diff::addFieldValue($field,$id);
							break;
					}
					break;
				case "add":
					$child_model = Model_Manager::create($field->type->config()->model_class,array('id' => $id,$field->type->config()->model_fk => $model->key()));
					switch($subaction) 
					{
						case "accept":
							$diffRenderer = new Helper_Diff_Renderer($child_model);
							$child_model = $diffRenderer->setModelChangesFromEdits();
							$field->value->add($child_model);
							$child_model->id = $id;
							Helper_Diff::deleteModelEdits($child_model);
							Helper_Diff::deleteFieldValue($field,$id);
							$child_model->id = 0;
							break;
						case "cancel":
							Helper_Diff::deleteModelEdits($child_model);
							Helper_Diff::deleteFieldValue($field,$id);
							break;
					}
					break;
				case "edit":
					if (!$child_model)
						break;
					$diffRenderer = new Helper_Diff_Renderer($child_model);
					$prefix = get_class($child_model)."-".$child_model->key()."-";
					$child_model = $diffRenderer->setModelChangesFromInput($input, $prefix);
					$field->value = $field->value->filter(array("id!=" => $id));
					$field->value->add($child_model);
					break;
			}
			 
		}
		$field->type->setNewValue($model,$field,$field->value);
	}
}

class Helper_Diff_Renderer
{
	private $model;
	
	public function model()
	{
		return $this->model;
	}
	
	public function parentName()
	{
		return $this->model()->modelName()."-".$this->model()->key()."-";
	}
	
	public function Helper_Diff_Renderer($model)
	{
		$this->model = $model;
	}
	
	public function renderModel()
	{
		$modelRenderer = new Model_Renderer();
		return $modelRenderer->render($this->model);
	}
	
	public function getModelEdits()
	{
		$edits = Model_Collection_Manager::byQuery(
			'Edit',
			Query::instance()
				->where('model_class',get_class($this->model))
				->where('model_id',$this->model->id)
		);
		foreach($edits as $k => $edit)
		{
			$edits[$k]->data('diff', Helper_Diff_Renderer::getDiff($edit));
		}
		return $edits;
	}	
	
	public static function getDiff($edit)
	{
		$rows = DDS::Execute(
				Query::instance()
					->select("Edit_Field.name","Edit_Value.value")
					->from("Edit_Value")
					->innerJoin("Edit_Field","Edit_Field.id=Edit_Value.Edit_Field__id")
					->where('Edit_Field.Edit__id',$edit->id)
		)->getResult()->asTable();
		$values = array();
		foreach($rows as $row)
		{
			$name = $row['name'];
			if (array_key_exists($name, $values))
			{
			} 
			else
			{
				$values[$name] = new Objective();
				$values[$name]->value = array();
			}
			$values[$name]->value[] = $row['value'];
		}
		return new Objective($values);
	}
	
	protected function getFields()
	{
		$edits = $this->getModelEdits();
		$model_class = get_class($this->model);
		$fields = array();
		foreach(Helper_Diff::config()->$model_class->fields as $fieldName => $fieldConfig)
		{
			$fieldType = Helper_Diff_Field_Factory::getFieldType($fieldConfig);
			$diff_values = array();
			foreach($edits as $edit)
			{
				$diff = $edit->data('diff');
				if ($diff->$fieldName)
				$diff_values[$edit->id] = array( 'edit' => $edit, 'value' => $diff->$fieldName->value );
			}
			if (count($diff_values)>0)
				$fields[] = new Objective(array(
								'name' => $fieldName,
								'type' => $fieldType,
								'renderer' => Helper_Diff_Field_Factory::getFieldRenderer($fieldType),
								'value' => $this->model->$fieldName,
								'edits' => $diff_values
				));
		}
		return $fields;
	}
	
	public function render($parent = null)
	{
		return Controller_Manager::htmlUncached(
			"Diff_Renderer/index", 
			array(
				'parent'	=> $parent,
				'renderer' => $this,
				'fields' => $this->getFields()
			)
		);
	}
	
	public function setModelChangesFromEdits()
	{
		$fields = $this->getFields();
		$this->model()->id = 0;
		foreach($fields as $field)
		{
			$input = $field->renderer->makeInput(array_keys($field->edits->__toArray()),$field);
			$dt = new Data_Transport();
			$t = $dt->beginTransaction();
			$t->send($input);
			$t->commit();
			$field->renderer->updateFromInput($this->model(), $field, $dt);
		}
		$this->model()->saveCarefully();
		return $this->model();	
	}
	public function setModelChangesFromInput($input,$parent = '')
	{
		$fields = $this->getFields();
		foreach($fields as $field)
		{
			$field->renderer->updateFromInput($this->model(), $field, $input, $parent);
		}
		$this->model()->saveCarefully();
		return $this->model();	
	}
}