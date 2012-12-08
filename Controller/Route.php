<?php

/**
 * Контроллер для обновления роутов
 * 
 * @author morph
 */
class Controller_Route extends Controller_Abstract
{
    /**
     * Обновляет роуты
     */
    public function update($context)
    {
        $this->task->setTemplate(null);
        $user = $context->user->getCurrent();
        if ($user->key() >= 0 && !$user->hasRole('editor')) {
            return;
        }
        $paths = IcEngine::getLoader()->getPaths('Controller');
        $routes = array();
        $controllers = array();
        foreach ($paths as $path) {
            if (!$path || !is_dir($path)) {
                continue;
            }
            ob_start();
            system('find ' . $path . '** | grep .php');
            $content = ob_get_contents();
            ob_end_clean();
            $files = explode(PHP_EOL, $content);
            foreach ($files as $file) {
                if (!$file || !is_file($file)) {
                    continue;
                }
                $content = file_get_contents($file);
                if (strpos($content, 'namespace IcEngine\\') !== false) {
                    continue;
                }
                $matches = array();
                preg_match_all(
                    '#class\s+(Controller_[A-Z][A-Za-z_0-9]+)#', 
                    $content, 
                    $matches
                );
                if (empty($matches[1][0])) {
                    continue;
                }
                $controllers[] = array(
                    'class' => $matches[1][0],
                    'file'  => $file
                );
            }
        }
        if (!$controllers) {
            return;
        }
        $routeIds = array();
        $annotationManager = IcEngine::serviceLocator()->getSource()
            ->getAnnotationManager();
        foreach ($controllers as $i => $controller) {
            $controllerName = substr(
                $controller['class'], strlen('Controller_')
            );
            echo '#' . ($i + 1) . ' ' . $controller['class'] . 
                ' (' . $controller['file'] . ') done.' . PHP_EOL;
            $annotation = $annotationManager->getAnnotation(
                $controller['class']
            )->getData();
            $methodAnnotations = $annotation['methods'];
            if (!$methodAnnotations) {
                continue;
            }
            foreach ($methodAnnotations as $methodName => $data) {
                if (!$data) {
                    continue;
                }
                $hasAnnotation = false;
                foreach (array_keys($data) as $annotationName) {
                    if (strpos($annotationName, 'Route') === 0) {
                        $hasAnnotation = true;
                        break;
                    }
                }
                if (!$hasAnnotation) {
                    continue;
                }
                $route = reset($data['Route'][0]);
                $patterns = array();
                if (!empty($data['RouteComponent'])) {
                    foreach ($data['RouteComponent'] as $routeComponent) {
                        $componentName = reset($routeComponent);
                        unset($routeComponent[$componentName]);
                        $patterns[$componentName] = $routeComponent;
                    }
                }
                $weight = !empty($data['RouteWeight'])
                    ? reset($data['RouteWeight'][0]) : 0;
                $params = array();
                if (!empty($data['RouteParam'])) {
                    foreach ($data['RouteParam'] as $routeParam) {
                        $paramName = reset($routeParam);
                        $paramValue = $routeParam['value'];
                        $params[$paramName] = $paramValue;
                    }
                }
                $actions = array($controllerName . '/' . $methodName);
                if (!empty($data['RouteAction'])) {
                    foreach ($data['RouteAction'] as $routeAction) {
                        $actions[] = reset($routeAction);
                    }
                }
                $routeName = !empty($data['RouteName'])
                    ? reset($data['RouteName'][0]) : null;
                $routeData = array(
                    'route'     => $route,
                    'weight'    => $weight, 
                    'actions'   => $actions
                );
                $routeIds[] = $route;
                if ($params) {
                    $routeData['params'] = $params;
                }
                if ($patterns) {
                    $routeData['patterns'] = $patterns;
                }
                if ($routeName) {
                    $routes[$routeName] = $routeData;
                } else {
                    $routes[] = $routeData;
                }
            }
        }
        $config = $context->configManager->get('Route')->__toArray();
        $emptyRoute = isset($config['empty_route']) 
            ? $config['empty_route'] : array();
        if (!empty($config['routes'])) {
            foreach ($config['routes'] as $routeName => $route) {
                if (in_array($route['route'], $routeIds)) {
                    continue;
                }
                if (is_numeric($routeName)) {
                    $routes[] = $route;
                } else {
                    $routes[$routeName] = $route;
                }
            }
        }
        ksort($routes);
        $output = Helper_Code_Generator::fromTemplate (
            'route',
            array (
                'routes'        => $routes,
                'empty_route'   => $emptyRoute
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
        $filename = IcEngine::root() . 'Ice/Config/Route.php';
        file_put_contents($filename, implode(PHP_EOL, $result));
    }
}