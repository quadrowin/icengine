<?php

class Component_Geo_Polyline extends Model_Component
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