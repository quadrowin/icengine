<?php

class Filter_Numeric implements Filter_Interface
{
	public function apply ($data)
	{
		return (int) $data;
	}
}