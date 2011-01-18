<?php

class View_Resource_Loader
{
	/**
	 * 
	 * @param string|array <string> $dirs
	 */
	public static function load ($base_url, $base_dir, $dirs)
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
			$options = array (
				'source'	=> $dirs [$i],
				'nopack'	=> ($dirs [$i][0] == '-')
			);
			
			if ($options ['nopack'])
			{
				$dirs [$i] = substr ($dirs [$i], 1);
			}
			
			$dbl_star_pos = strpos ($dirs [$i], '**');
			$star_pos = strpos ($dirs [$i], '*');
			
			if ($dbl_star_pos !== false)
			{
			    // Путь вида "js/**.js"
			    // Включает поддиректории.
			    
			    // $dirs [i] = "js/**.js"
				$dir = trim (substr ($dirs [$i], 0, $dbl_star_pos), '/');
			    // $dir = "js"
				$pattern = substr ($dirs [$i], $dbl_star_pos + 1);
				// $pattern = "*.js"
				
				$subdirs = scandir ($base_dir . '/' . $dir);
				
				$list = array (
				    explode ('/', $dir)
				);
				
				$files = array ();
				
				for ($p = current ($list); $p; $p = next ($list))
				{
					$dir = implode ('/', $p);
					
					$subdirs = scandir ($base_dir . $dir);
					
					for ($j = 0, $count = sizeof ($subdirs); $j < $count; $j++)
					{
					    if (
					        $subdirs [$j][0] == '.' ||
					        $subdirs [$j][0] == '_'
					    )
						{
						    continue;
						}
					    
						$fn = $base_dir . $dir . '/' . $subdirs [$j];
						
						if (!is_dir ($fn))
						{
						    if (fnmatch ($pattern, $fn))
					        {
						        $files [] = $base_url . $dir . '/' . $subdirs [$j];
					        }
							continue;
						}
						
						array_push ($list, 
							explode ('/', $dir . '/' . $subdirs [$j])
						);
					}
				}
				
				for ($j = 0, $count = sizeof ($files); $j < $count; $j++)
				{
					$options ['source'] = $files [$j];
					View_Render_Broker::getView ()
						->resources ()
							->add ($files [$j], null, $options);
				}
			}
			elseif ($star_pos !== false)
			{
			    // Путь вида "js/*.js"
			    // Включает файлы, подходящие под маску в текущей директории
			    
			    // $dirs [i] = "js/**.js"
				$dir = trim (substr ($dirs [$i], 0, $star_pos), '/');
			    // $dir = "js"
				$pattern = substr ($dirs [$i], $star_pos);
				// $pattern = "*.js"
			    
				$iterator = new DirectoryIterator (
				    rtrim ($base_dir, '/') . '/' . $dir
				);
				
				foreach ($iterator as $file)
				{
				    $fn = $file->getFilename ();
					if (
					    $file->isFile () &&
					    $fn [0] != '.' && 
					    $fn [0] != '_' &&
					    fnmatch ($pattern, $file)
					)
					{
						$fn = 
							rtrim ($base_url, '/') . '/' .
							rtrim ($dir, '/') . '/' . 
							$fn;
						$options ['source'] = $fn;
						View_Render_Broker::getView ()
							->resources ()
								->add ($fn, null, $options);
					}
				}
			}
			else
			{
			    // Указан путь до файла: "js/scripts.js"
				$file = $base_url . $dirs [$i];
				View_Render_Broker::getView ()
					->resources ()
						->add ($file, null, $options);
			}
		}
	}
}