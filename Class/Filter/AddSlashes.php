<?php

class Filter_AddSlashes implements Filter_Interface
{
	
	public function apply ($data)
	{
		return addslashes ($data);
	}
	
}