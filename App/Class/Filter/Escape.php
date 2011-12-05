<?php

namespace Ice;

class Filter_Escape
{

	public function filter ($data)
	{
		return DDS::escape ($data);
	}

}