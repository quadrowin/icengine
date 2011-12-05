<?php

class Model_Mapper_Scheme_Render_Mysql
{
	public function render ($scheme)
	{
		$sql = 'CREATE TABLE IF NOT EXISTS `' .
			Model_Scheme::table ($scheme->getModel ()->modelName ()). '` (' . "\n";
		$fields = $scheme->getFields ();
		if ($fields)
		{
			foreach ($fields as $field)
			{
				$sql .= "\t" . '`' . $field->getField () . '` ' . $field->getName ();
				$attributes = $field->getAttributes ();

				if (isset ($attributes ['Max_Length']))
				{
					$max_length = $attributes ['Max_Length']->getValue ();
					if ($max_length)
					{
						$sql .= '(' . $max_length . ') ';
					}
				}
				if (isset ($attributes ['Not_Null']))
				{
					$sql .= ($attributes ['Not_Null']->getValue () ? 'NOT ' : '') .
						'NULL ';
				}
				if (isset ($attributes ['Default_Value']))
				{
					$sql .= 'DEFAULT \'' .
						str_replace ('\'', '',
							$attributes ['Default_Value']->getValue ()) .
						'\' ';
				}
				if (isset ($attributes ['Encoding']))
				{
					$sql .= 'CHARACTER SET ' .
						str_replace ('-', '', strtolower (
							$attributes ['Encoding']->getValue ())) . ' ';
				}
				if (isset ($attributes ['Auto_Increment']))
				{
					$auto_increment = $attributes ['Auto_Increment']->getValue ();
					if ($auto_increment)
					{
						$sql .= 'AUTO_INCREMENT ';
					}
				}
				if (isset ($attributes ['Comment']))
				{
					$comment = $attributes ['Comment']->getValue ();
					if ($comment)
					{
						$sql .= 'COMMENT \'' . str_replace ('\''. '',
							$comment) . '\'';
					}
				}
				$sql .= ",\n";
			}
		}

		$keys = $scheme->getKeys ();
		if ($keys)
		{
			foreach ($keys as $key)
			{
				$key_name = $key->getName ();
				$sql .=
					($key_name != 'Index' ? $key_name . ' KEY ' . '  '  : $key_name) .
					($key_name != 'Primary' ? '`' . $key->getField () . '`'  : '') .
					' (';
				$values = (array) $key->getValue ();
				foreach ($values as &$value)
				{
					$value = '`' . $value . '`';
				}
				$values = implode (',', $values);
				$sql .= $value .'),' . "\n";
			}
		}
		$sql = substr ($sql, 0, -2);
		$settings = $scheme->getSettings ();
		if ($settings)
		{
			if (isset ($settings ['Engine']))
			{
				$engine = $settings ['Engine']->getValue ();
				$sql .= 'ENGINE=' . $engine . ' ';
			}
			if (isset ($settings ['Default_Encoding']))
			{
				$sql .= 'DEFAULT CHARSET=' . str_replace ('-', '', strtolower (
					$attributes ['Default_Encoding']->getValue ())) . ' ';
			}
			if (isset ($settings ['Comment']))
			{
				$comment = $settings ['Comment']->getValue ();
				if ($comment)
				{
					$sql .= 'COMMENT \'' . str_replace ('\''. '',
						$comment) . '\'';
				}
			}
		}
		$sql .= ')' . "\n";

		return $sql;
	}
}