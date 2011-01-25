<?php

class Filter_Trim implements Filter_Interface
{
	public function apply ($data, $characters = null)
	{
		return trim ($data, $characters);
	}
}