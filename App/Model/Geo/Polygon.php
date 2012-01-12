<?php

namespace Ice;

class Geo_Polygon extends Model
{
	public function points ()
	{
		return $this->component ('Geo_Point');
	}

	public function style ()
	{
		return $this->Geo_Line_Style->name;
	}
}