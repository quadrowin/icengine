<?php

class Filter_AddSlashes
{
	
	public function filter ($data)
	{
		return addslashes ($data);
	}
	
}