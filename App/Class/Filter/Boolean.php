<?php

namespace Ice;

class Filter_Boolean
{

	public function filter ($data)
	{
		return (bool) $data;
	}

}