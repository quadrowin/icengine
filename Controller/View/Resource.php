<?php

/**
 * Контроллер для компновки ресурсов представления.
 * Предназначен для сбора js, css файлов в один.
 *
 * @author goorus, morph
 */
class Controller_View_Resource extends Controller_Abstract
{
	/**
     * Процесс упаковки ресурсов
     *
     * @Context("configManager")
     * @Context("viewResourceManager")
     */
	public function index($type, $params, $name, $context)
	{
		$vars = array ();
		if ($params) {
			foreach ($params as $key => $value) {
				$vars['{$' . $key . '}'] = $value;
			}
		}
        $resultResources = array();
		$moduleCollection = $context->collectionManager->create('Module');
		foreach ($moduleCollection as $module) {
            $configName = 'Controller_View_Resource';
            $mainModule = false;
            if (!$module->isMain) {
                $configName = 'Module_' . $module->name. '_' . $configName;
            } else {
                $mainModule = true;
            }
			$config = $context->configManager->get($configName);
			if (!$mainModule && empty($module['hasResource'])) {
				continue;
			}
			$vars['{$moduleName}'] = $module->name;
			$vars['{$modulePath}'] = $module->path;
			if (!$config || !$config->targets) {
				return;
			}
			foreach ($config->targets as $targetName => $target) {
				if ($type && $type != $target->type) {
                    continue;
                }
                if ($name && $name != $targetName) {
                    continue;
                }
				$resources = array();
				foreach ($target->sources as $source) {
					if (is_string($source)) {
						$sourceDir = IcEngine::root();
						$sourceFiles = array($source);
					} else {
						$sourceDir = strtr($source->dir, $vars);
						$sourceFiles = is_scalar($source->file)
							? array($source->file)
                            : $source->file->__toArray();
					}
					foreach ($sourceFiles as $filename) {
                        $filename = strtr($filename, $vars);
                        $loadedResources = $context->viewResourceManager->load(
                            '/', $sourceDir, array($filename), $target->type . $module->name
                        );
						$resources = array_merge(
							$resources, (array)$loadedResources
						);
					}
				}
                $existsResources = array();
                $resultResources = array();
                foreach ($resources as $resource) {
                    if (in_array($resource->filePath, $existsResources)) {
                        continue;
                    }
                    $resultResources[] = $resource;
                    $existsResources[] = $resource->filePath;
                }
				$packer = $context->viewResourceManager->packer($target->type);
				$packerConfig = $target->packer_config;
				if ($packerConfig && $packerConfig->state_file) {
					$packerConfig->state_file = strtr(
						$packerConfig->state_file, $vars
					);
				}
				$destinationFile = strtr($target->file, $vars);
				$packer->pushConfig($packerConfig);
				$packer->pack(
                    $resultResources, $destinationFile, $packerConfig, true
                );
				$packer->popConfig();
                $resultResources[$name] = array(
					'type'	=> $target->type,
					'url'	=> strtr($target->url, $vars),
					'ts'	=> $packer->cacheTimestamp()
				);
			}
			$this->output->send('resources', $resultResources);
		}
	}
}