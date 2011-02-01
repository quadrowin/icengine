<?php

class Filter_Trim
{
	
	public function filter ($data)
	{
		return trim ($data);
	}
	
	public function filterEx ($field, $data, stdClass $scheme)
	{
		$chars = 
			isset ($scheme->field ['trimChars']) ? 
			$scheme->field ['trimChars'] : null;
		return trim ($data->$field, $chars);
	}
	
}