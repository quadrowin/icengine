<?php

class View_Resource_Loader
{
	/**
	 * 
	 * @param string|array <string> $dirs
	 */
	public static function load ($base_url, $base_dir, $dirs, $type = null)
	{
		$base_dir = str_replace ('\\', '/', $base_dir);
		$base_dir = rtrim ($base_dir, '/') . '/' ;
		
		if (!$base_url)
		{
			$base_url = $base_dir;
		}
		
		foreach ($dirs as $pattern)
		{
			$options = array (
				'source'	=> $pattern,
				'nopack'	=> ($pattern [0] == '-'),
				'filePath'	=> ''
			);
			
			if ($options ['nopack'])
			{
				$pattern = substr ($pattern, 1);
			}
			
			$dbl_star_pos = strpos ($pattern, '**');
			$star_pos = strpos ($pattern, '*');
			
			if ($dbl_star_pos !== false)
			{
			    // Путь вида "js/**.js"
			    // Включает поддиректории.
			    
			    // $dirs [i] = "js/**.js"
				$dir = trim (substr ($pattern, 0, $dbl_star_pos), '/');
			    // $dir = "js"
				$pattern = substr ($pattern, $dbl_star_pos + 1);
				// $pattern = "*.js"
				
				$list = array (
				    $dir
				);
				
				$files = array ();
				
				for ($dir = reset ($list); $dir; $dir = next ($list))
				{
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
						
						if (is_dir ($fn))
						{
							array_push ($list, $dir . '/' . $subdirs [$j]);
						}
						elseif (fnmatch ($pattern, $fn))
				        {
					        $files [] = array (
					        	$base_url . $dir . '/' . $subdirs [$j],
					        	$base_dir . $dir . '/' . $subdirs [$j]
					        );
				        }
					}
				}
				
				$base_dir_len = strlen ($base_dir);
				for ($j = 0, $count = sizeof ($files); $j < $count; $j++)
				{
					$options ['source'] = $files [$j][0];
					$options ['filePath'] = $files [$j][1];
					$options ['localPath'] = substr (
						$files [$j][1],
						$base_dir_len
					);
					View_Render_Broker::getView ()
						->resources ()
							->add ($files [$j][0], $type, $options);
				}
			}
			elseif ($star_pos !== false)
			{
			    // Путь вида "js/*.js"
			    // Включает файлы, подходящие под маску в текущей директории
			    
			    // $dirs [i] = "js/*.js"
				$dir = trim (substr ($pattern, 0, $star_pos), '/');
			    // $dir = "js"
				$pattern = substr ($pattern, $star_pos);
				// $pattern = "*.js"
			    
				$iterator = new DirectoryIterator ($base_dir . '/' . $dir);
				
				foreach ($iterator as $file)
				{
				    $fn = $file->getFilename ();
					if (
					    $file->isFile () &&
					    $fn [0] != '.' && 
					    $fn [0] != '_' &&
					    fnmatch ($pattern, $fn)
					)
					{
						$local_path = $dir . '/' . $fn;
						$options ['source'] = $base_url . $local_path;
						$options ['filePath'] = $base_dir . $local_path;
						$options ['localPath' ] = $local_path;
						View_Render_Broker::getView ()
							->resources ()
								->add (
									$base_url . $local_path,
									$type, $options
								);
					}
				}
			}
			else
			{
			    // Указан путь до файла: "js/scripts.js"
				$file = $base_url . $pattern;
				$options ['filePath'] = $base_dir . $pattern;
				$options ['localPath'] = $pattern;
				View_Render_Broker::getView ()
					->resources ()
						->add ($file, $type, $options);
			}
		}
	}
}