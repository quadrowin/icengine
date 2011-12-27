<?php

namespace Ice;

class Model_Mapper_Scheme_Render
{
	public function render ($name, $scheme)
	{
		$class_name = 'Model_Mapper_Scheme_Render_' . $name;
		Loader::load ($class_name);
		return $class_name::render ($scheme);
	}
}