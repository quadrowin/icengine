<?php

class Filter_UpperCase implements Filter_Interface
{
	public function apply ($data)
	{
		return mb_strtoupper ($data);
	}
}