<?php

class View_Resource_Loader
{
	/**
	 * 
	 * @param string|array <string> $dirs
	 */
	public function load ($base_url, $base_dir, $dirs)
	{
		$dirs = array_values ((array) $dirs);
		
		$base_dir = str_replace ('\\', '/', $base_dir);
		$base_dir = rtrim ($base_dir, '/') . '/' ;
		
		if (!$base_url)
		{
			$base_url = $base_dir;
		}
		
		for ($i = 0, $icount = sizeof ($dirs); $i < $icount; $i++)
		{
			if (strpos ($dirs [$i], '*') === false)
			{
				$iterator = new DirectoryIterator (rtrim ($base_dir, '/'). '/' .$dirs [$i]);
				foreach ($iterator as $file)
				{
					if (!$file->isDot () && $file->isFile ())
					{
						View_Render_Broker::getView()
							->resources()
								->add (
									rtrim ($base_url, '/') . '/' .
									rtrim ($dirs [$i], '/') . '/' . 
								  	$file->getFilename ()
								);
					}
				}
			}
			else
			{
				$dir = trim (str_replace ('*', '', $dirs [$i]), '/');
				
				$subdirs = scandir ($base_dir . '/' . $dir);
				
				$list = array ();
				
				$list [0] = explode ('/', $dir);
				$st = sizeof ($list [0]) - 1;
				
				$n = 0;
				
				$files = array ();
				
				while ($p = current ($list))
				{
					$dir = join ('/', $p);
					$subdirs = scandir ($base_dir . $dir);
					for ($i = 0, $count = sizeof ($subdirs); $i < $count; $i++)
					{
						$files [] = $base_url. $dir . '/' . $subdirs [$i];
						if (
							$subdirs [$i][0] == '.' ||
							$subdirs [$i] == '.' || 
							$subdirs [$i] == '..' || 
							!is_dir ($base_dir . $dir.'/'.$subdirs [$i])
						)
						{
							continue;
						}
						array_push ($list, 
							explode ('/', $dir.'/'.$subdirs [$i])
						);
					}
					$n++;
					next ($list);
				}
				for ($i = 0, $count = sizeof ($files); $i < $count; $i++)
				{
					View_Render_Broker::getView()
						->resources()
							->add ($files [$i]);
				}
				
			}
		}
	}
}