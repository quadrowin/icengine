<?php

class Filter_LowerCase implements Filter_Interface
{
	public function apply ($data)
	{
		return mb_strtolower ($data);
	}
}