<?php

namespace Ice;

class Filter_Empty_Default
{

	public function filter ($data)
	{
		return $data ? $data : '';
	}

	/**
	 * @desc 
	 * @param string $field
	 * @param Objective $data
	 * @param Objective $scheme
	 */
	public function filterEx ($field, $data, $scheme)
	{
		$default =
			isset ($scheme->field ['default']) ?
				$scheme->field ['default'] :
				'';
		return $data ? $data : $default;
	}

}