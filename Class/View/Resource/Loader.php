<?php

class View_Resource_Loader
{
	/**
	 * 
	 * @param string|array <string> $dirs
	 */
	public function load ($dirs)
	{
		$dirs = array_values ((array) $dirs);
		
		for ($i = 0, $icount = sizeof ($dirs); $i < $icount; $i++)
		{
			$iterator = new DirectoryIterator ($dirs [$i]);
			foreach ($iterator as $file)
			{
				if (!$file->isDot () && $file->isFile ())
				{
					View_Render_Broker::getView()
						->resources()
							->add (rtrim ($dirs [$i], '/') . '/' .
							  $file->getFilename ());
				}
			}
		}
	}
}