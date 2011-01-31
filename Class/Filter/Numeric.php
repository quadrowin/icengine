<?php

class Filter_Numeric
{
	public function filter ($data)
	{
		return (int) $data;
	}
}