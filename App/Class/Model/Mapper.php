<?php

/**
 * @desc ORM
 * @author Илья Колесников
 */
class Model_Mapper
{
	protected static $_modelName;

	public static function field ($field_name, $field_attributes)
	{
		$field = Model_Mapper_Field::factory ($field_name);

		foreach ($field_attributes as $name => $value)
		{
			$attribute = Model_Mapper_Field_Attribute::factory ($name);
			$attribute->setValue ($value);
			$field->addAttribute ($attribute);
		}
		return $field;
	}

	public static function index ($index_name, $index_fields)
	{
		$index = Model_Mapper_Index::factory ($index_name);
		$index->setField ($index_name);
		$index->setValue ($index_fields);
		return $index;
	}

	public function model ($model_name)
	{
		self::$_modelName = $model_name;
		$scheme = Model_Scheme::getScheme ($model_name);
		if (!$scheme)
		{
			Loader::load ($model_name);
			$model = new $model_name;
			$model->scheme ()->setModel ($model);
			$model->scheme ()->load ();
			$raw = $model->scheme ()->render ();
			echo $raw;
			$ds = Model_Scheme::dataSource ($model_name);
			$adapter = $ds->getAdapter ();
			$adapter->setTranslatedQuery ($raw);
			$adapter->_executeChange (
				Query::instance (),
				new Query_Options
			);
			if (method_exists ($adapter, 'getCacher'))
			{
				$adapter->getCacher ()->tagDelete (
					Model_Scheme::table ($model_name)
				);
			}
		}
		return self;
	}
}