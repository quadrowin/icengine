<?php

/**
 * Контроллер для аннотаций типа "Cache"
 * 
 * @author morph
 */
class Controller_Annotation_Cache extends Controller_Abstract
{
    /**
     * Распарсить аннотацию
     * 
     * @Context("helperCodeGenerator")
     */
    public function update($data, $context) 
    {
        $caches = array();
        $config = $context->configManager->get('Controller_Manager');
        foreach ($data as $id => $subData) {
            list($preControllerName, $methodName) = explode('/', $id);
            if (!$subData) {
                continue;
            }
            $controllerName = str_replace(
                'Controller_', '', $preControllerName
            );
            $hasAnnotation = false;
            foreach (array_keys($subData) as $annotationName) {
                if (strpos($annotationName, 'Cache') === 0) {
                    $hasAnnotation = true;
                    break;
                }
            }
            if (!$hasAnnotation) {
                continue;
            }
            $cache = reset($subData['Cache']['data'][0]);
            if (!$cache) {
                continue;
            }
            $expiration = !empty($subData['CacheExpiration']['data'])
                ? reset($subData['CacheExpiration']['data'][0]) : 0;
            $profile = !empty($cache['profile'])
                ? $cache['profile'] : null;
            if ($profile) {
                $profile = $config->profiles[$profile];
                if ($profile) {
                    $expiration = $profile['expiration'];
                }
            }
            if (!$expiration) {
                continue;
            }
            $tags = array();
            if (!empty($subData['CacheTags']['data'])) {
                $tags = array_values($subData['CacheTags']['data'][0]);
            }
            $vars = array();
            if (!empty($data['CacheVars'])) {
                $vars = array_values($data['CacheVars']['data'][0]);
            }
            $key = $controllerName . '::' . $methodName;
            $theCache = array(
                'action'        => $key,
                'expiration'    => $expiration, 
                'tags'          => $tags,
                'vars'          => $vars
            );
            $caches[$key] = $theCache;
        }
        $profiles = $config['profiles'];
        if ($profiles) {
            $profiles = $profiles->__toArray();
        }
        if (!empty($config['actions'])) {
            foreach ($config['actions']->__toArray() as $actionName => $data) {
                if (isset($caches[$actionName])) {
                    continue;
                }
                $data['action'] = $actionName;
                $caches[$actionName] = $data;
            }
        }
        ksort($caches);
        $output = $context->helperCodeGenerator->fromTemplate(
            'controllerCache',
            array (
                'actions'   => $caches,
                'profiles'  => $profiles
            )
        );
        $result = array();
        $lines = explode(PHP_EOL, $output);
        foreach ($lines as $i => $line) {
            $baseLine = $line;
            $line = str_replace(array("\n", "\r"), '', trim($line));
            if (!$line) {
                continue;
            }
            $result[] = $baseLine;
        }
        $filename = IcEngine::root() . 'Ice/Config/Controller/Manager.php';
        file_put_contents($filename, implode(PHP_EOL, $result));
    }
}