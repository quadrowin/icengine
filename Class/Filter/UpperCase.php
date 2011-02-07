<?php

class Filter_UpperCase
{
	
	public function filter ($data)
	{
		return mb_strtoupper ($data);
	}
	
}