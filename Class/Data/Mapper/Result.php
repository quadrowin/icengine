<?php

class Data_Mapper_Result
{
	/**
	 * @desc Модели
	 * @var array
	 */
	protected static $_models;

	/**
	 * @desc Получить имя модели
	 * @param string $model_name
	 * @return string
	 */
	public function get ($name)
	{
		if (is_null (self::$_models))
		{
			self::$_models = Resource_Manager::get (
				'Data_Mapper', 'Models'
			);
		}

		if (is_null (self::$_models))
		{
			self::$_models = array ();

			$models = Model_Scheme::$models;

			$default = Model_Scheme::$default;
			$default_prefix = $default ['prefix'];

			foreach ($models as $model_name => $data)
			{
				$table = isset ($data ['table'])
					? $data ['table'] : $model_name;

				$model_name = implode ('_', array_map (
					'ucfirst',
					explode ('_', $model_name)
				));

				$prefix = '';
				if (isset ($data ['table']))
				{
					$prefix = isset ($data ['prefix'])
						? $data ['prefix'] : '';
				}
				else
				{
					$prefix = isset ($data ['prefix'])
						? $data ['prefix'] : $default_prefix;
				}
				self::$_models [$model_name] = $prefix . $table;
			}

			Loader::load ('Helper_Data_Source');
			$tables = Helper_Data_Source::tables ();

			foreach ($tables as $table)
			{
				$table_name = $table ['Name'];
				if (strpos ($table_name, $default_prefix) === 0)
				{
					$table_name = substr (
						$table_name,
						strlen ($default_prefix)
					);
				}

				$table_name = implode ('_', array_map (
					'ucfirst',
					explode ('_', $table_name)
				));

				if (!isset (self::$_models [$table_name]))
				{
					self::$_models [$table_name] = $table ['Name'];
				}
			}

			Resource_Manager::set (
				'Data_Mapper', 'Models', self::$_models
			);
		}

		return isset (self::$_models [$name])
			? self::$_models [$name] : null;
	}
}