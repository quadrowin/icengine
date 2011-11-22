<?php

class Helper_Code_Generator 
{
	protected static $_config = array (
		'base'					=> 'IcEngine/View/Code/Generator/',
		'controller_template'	=> 'controller.tpl',
		'action_template'		=> 'action.tpl',
		'model_template'		=> 'model.tpl',
		'collection_template'	=> 'collection.tpl',
	);
	
	public static function applyVars ($template, $vars)
	{
		foreach ($vars as $var => $value)
		{
			$template = str_replace (
				'{{' . $var . '}}',
				$value,
				$template
			);
		}
		
		return $template;
	}
	
	public static function phpName ($root, $name)
	{
		
		return rtrim ($root, '/') . '/' . str_replace ('_', '/', $name) . '.php';
	}
	
	public static function tplName ($controller, $action)
	{
		$root = rtrim (IcEngine::root (), '/') . '/Ice/View/Controller/';
		$root .= str_replace ('_', '/', $controller);
		
		return $root . '/' . $action . '.tpl';
	}
	
	public static function config ()
	{
		return Config_Manager::get (__CLASS__, self::$_config);
	}
	
	public static function loadTemplate ($file_name)
	{
		return file_get_contents ($file_name);
	}
	
	public static function appendTemplate ($content, $part)
	{
		return $content . ($content ? PHP_EOL . PHP_EOL : '') . $part;
	}
	
	public static function saveTemplate ($file_name, $content)
	{
		$dirname = dirname ($file_name);

		if (!is_dir ($dirname))
		{
			mkdir ($dirname, 0755, true);
		}
		
		file_put_contents ($file_name, $content);
	}
	
	public static function tpl ($file)
	{
		return IcEngine::root () . self::config ()->base . $file;
	}
	
	public static function createModel ($name)
	{
		$root = rtrim (IcEngine::root (), '/') . '/Ice/Model/';
		$file_name = self::phpName ($root, $name);
		
		if (is_file ($file_name))
		{
			return;
		}
		
		$model_template = self::tpl (self::config ()->model_template);
		if (!$model_template)
		{
			return;
		}
		
		$template = self::loadTemplate ($model_template);
		$template = self::applyVars (
			$template,
			array (
				'name'	=> $name
			)
		);
		
		self::saveTemplate ($file_name, $template);
		
		$file_name = self::phpName ($root, $name . '_Collection');
		
		$collection_template = self::tpl (self::config ()->collection_template);
		if (!$collection_template)
		{
			return;
		}
		
		$template = self::loadTemplate ($collection_template);
		$template = self::applyVars (
			$template,
			array (
				'name'	=> $name
			)
		);
		
		self::saveTemplate ($file_name, $template);
		
		$option_dir = dirname ($file_name);
	
		mkdir (rtrim ($option_dir, '/') . '/Option' , 0755);
		mkdir (rtrim ($option_dir, '/') . '/Filter' , 0755);
	}
	
	public static function createController ($name, $actions)
	{
		$root = rtrim (IcEngine::root (), '/') . '/Ice/Controller/';
		$file_name = self::phpName ($root, $name);
		
		if (is_file ($file_name))
		{
			return;
		}
		
		$template = self::tpl (self::config ()->controller_template);
		if (!$template)
		{
			return;
		}
		
		$template = self::loadTemplate ($template);
		$template = self::applyVars (
			$template,
			array (
				'name'	=> $name
			)
		);
		
		if ($actions)
		{
			$action_template = self::tpl (self::config ()->action_template);
			
			if ($action_template)
			{
				$action_template = self::loadTemplate ($action_template);
				$sub_template = '';
				
				foreach ($actions as $action)
				{
					$current_template = self::applyVars (
						$action_template,
						array (
							'name'	=> $action
						)
					);
					
					$sub_template = self::appendTemplate (
						$sub_template, 
						$current_template	
					);
					
					$tpl_file_name = self::tplName ($name, $action);
					if (!is_file ($tpl_file_name))
					{
						self::saveTemplate ($tpl_file_name, '');
					}
				}
				
				$template = self::applyVars (
					$template,
					array (
						'actions'	=> $sub_template
					)
				);
			}
		
		}
		
		self::saveTemplate ($file_name, $template);
	}
	
	public static function moveFiles ($config, $source, $dest, $diff_dir = '')
	{ 
		$source_handle = opendir ($source); 

		if ($diff_dir)
		{
			if (strpos ($diff_dir, '__project_name__') !== false)
			{
				$diff_dir = str_replace (
					'__project_name__',
					$config ['name'],
					$diff_dir
				);
			}
			
			mkdir ($dest . '/' . $diff_dir, 0755, true); 
		}

		while (($res = readdir ($source_handle)) !== false)
		{ 
			if ($res == '.' || $res == '..')
			{
				continue; 
			}

			if (is_dir ($source . '/' . $res))
			{ 
				self::moveFiles (
					$config, 
					$source . '/' . $res, 
					$dest, 
					$diff_dir . '/' . $res
				); 
			} 
			else 
			{ 
				$new_source = $source . '/' . $res;
				
				$res = str_replace (
					'__project_name__',
					$config ['name'],
					$res
				);
				
				copy (
					$new_source, 
					$dest . '/' . $diff_dir . '/' . $res
				); 
				
				$content = self::loadTemplate (
					$dest . '/' . $diff_dir . '/' . $res);
				
				$content = self::applyVars ($content, $config);
				
				self::saveTemplate (
					$dest . '/' . $diff_dir . '/' . $res, 
					$content
				);
			} 
		} 
	} 
	
	public static function createProject ($config)
	{
		$dir = rtrim (IcEngine::root (), '/') . '/';
		
		$template = isset ($config ['template'])
			? $config ['template'] : 'Simple';
		
		self::moveFiles (
			$config,
			$dir . 'IcEngine/Install/' . $template, 
			rtrim ($dir, '/')
		);
	}
}