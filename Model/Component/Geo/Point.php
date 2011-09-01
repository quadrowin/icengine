<?php

Loader::load ('Model_Component');

class Component_Geo_Point extends Model_Component
{
	
	public function style ()
	{
		return $this->Geo_Point_Style->name;
	}
	
	public function toJs ()
	{
		return array (
			'id'		=> $this->key (),
			'longitude'	=> $this->longitude,
			'latitude'	=> $this->latitude,
			'title'		=> $this->name
		);
	}
	
}