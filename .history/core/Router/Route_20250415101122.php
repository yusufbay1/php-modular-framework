<?php

namespace Router;

use Core\Http\Request;

class Route
{
    private static array $routes = [];
    private static mixed $pathNotFound = null;
    private static mixed $methodNotAllowed = null;
    private static string $prefix = '';

    public static function get($expression, $function): RouteDefinition
    {
        return self::addRoute('get', $expression, $function);
    }

    public static function post($expression, $function): RouteDefinition
    {
        return self::addRoute('post', $expression, $function);
    }

    public static function put($expression, $function): RouteDefinition
    {
        return self::addRoute('put', $expression, $function);
    }

    public static function delete($expression, $function): RouteDefinition
    {
        return self::addRoute('delete', $expression, $function);
    }

    public static function patch($expression, $function): RouteDefinition
    {
        return self::addRoute('patch', $expression, $function);
    }

    public static function request($expression, $function): RouteDefinition
    {
        return self::addRoute(['get', 'post'], $expression, $function);
    }

    public static function prefix($prefix, $callback): void
    {
        self::$prefix = $prefix;
        $callback();
        self::$prefix = '';
    }

    public static function getAll(): array
    {
        return self::$routes;
    }

    public static function pathNotFound($function): void
    {
        self::$pathNotFound = $function;
    }

    public static function methodNotAllowed($function): void
    {
        self::$methodNotAllowed = $function;
    }

    public static function url(string $name, array $params = []): ?string
    {
        foreach (self::$routes as $route) {
            if ($route->name === $name) {
                $url = $route->expression;
                foreach ($params as $key => $value) {
                    $url = preg_replace('/\{' . $key . '\}/', $value, $url);
                }
                return $url;
            }
        }
        return null;
    }

    public static function cacheRoutes(string $file): void
    {
        $export = serialize([
            'routes' => self::$routes,
            'pathNotFound' => self::makeSerializableCallback(self::$pathNotFound),
            'methodNotAllowed' => self::makeSerializableCallback(self::$methodNotAllowed),
        ]);

        file_put_contents($file, $export);
    }

    public static function loadCachedRoutes(string $file): void
    {
        if (!file_exists($file)) 
            return;

        $import = unserialize(file_get_contents($file));
        self::$routes = $import['routes'] ?? [];

        if (isset($import['pathNotFound']) && is_array($import['pathNotFound'])) {
            $callback = $import['pathNotFound'];
            if (is_string($callback[0])) 
                $callback[0] = new $callback[0]();
            self::$pathNotFound = $callback;
        }

        if (isset($import['methodNotAllowed']) && is_array($import['methodNotAllowed'])) {
            $callback = $import['methodNotAllowed'];
            if (is_string($callback[0])) 
                $callback[0] = new $callback[0]();
            self::$methodNotAllowed = $callback;
        }
    }

    private static function makeSerializableCallback($callback): mixed
    {
        if (is_array($callback) && is_string($callback[0])) 
            return $callback;
        return null; 
    }

    public static function run($basePath = '', $case_matters = false, $trailing_slash_matters = false, $multiMatch = false): void
    {
        $basePath = rtrim($basePath, '/');
        $parsed_url = parse_url($_SERVER['REQUEST_URI']);
        $path = $parsed_url['path'] ?? '/';

        if (!$trailing_slash_matters)
            $path = ($basePath . '/' !== $path) ? rtrim($path, '/') : $path;

        $path = urldecode($path);
        $method = $_SERVER['REQUEST_METHOD'];
        $path_match_found = false;
        $route_match_found = false;
        $request = Request::createFromGlobals();

        foreach (self::$routes as $route) {
            $expression = $route->expression;

            if (!empty($route->where))
                foreach ($route->where as $param => $regex)
                    $expression = str_replace('{' . $param . '}', "($regex)", $expression);
            else
                $expression = preg_replace('/\{[a-zA-Z_][a-zA-Z0-9_]*\}/', '([^/]+)', $expression);


            if ($basePath !== '' && $basePath !== '/')
                $expression = '(' . $basePath . ')' . $expression;

            $expression = '#^' . $expression . '$#u' . ($case_matters ? '' : 'i');

            if (preg_match($expression, $path, $matches)) {
                $path_match_found = true;
                $allowedMethods = array_map('strtolower', (array)$route->methods);

                if (in_array(strtolower($method), $allowedMethods)) {
                    array_shift($matches);
                    if ($basePath !== '' && $basePath !== '/') {
                        array_shift($matches);
                    }

                    foreach ($route->middleware as $middlewareClass) {
                        $middleware = new $middlewareClass();
                        if (method_exists($middleware, 'handle')) {
                            $response = $middleware->handle($request);
                            if ($response !== true) {
                                echo $response;
                                return;
                            }
                        }
                    }

                    $arguments = array_merge([$request], $matches);
                    $callback = is_array($route->function)
                        ? [new $route->function[0], $route->function[1]]
                        : $route->function;

                    $paramNames = [];
                    preg_match_all('/\{(\w+)\}/', $route->expression, $paramNames);
                    if (isset($paramNames[1])) {
                        $routeParamsAssoc = array_combine($paramNames[1], $matches);
                        $request->setRouteParams($routeParamsAssoc);
                    }
                    $result = call_user_func_array($callback, $arguments);

                    if (is_array($result) || is_object($result)) {
                        header('Content-Type: application/json');
                        echo json_encode($result);
                    } elseif (!is_null($result)) {
                        header('Content-Type: text/html');
                        echo $result;
                    }

                    $route_match_found = true;
                    if (!$multiMatch) {
                        break;
                    }
                }
            }
        }
        if (!$route_match_found) {
            $callback = $path_match_found ? self::$methodNotAllowed : self::$pathNotFound;
            if ($callback) {
                if (is_array($callback) && is_string($callback[0])) {
                    $callback[0] = new $callback[0]();
                }
                echo call_user_func($callback, $path, $path_match_found ? $method : null);
            }
        }
    }

    private static function addRoute($method, $expression, $function): RouteDefinition
    {
        $route = new RouteDefinition();
        $route->methods = (array)$method;
        $route->expression = self::$prefix . $expression;
        $route->function = $function;
        self::$routes[] = $route;
        return $route;
    }
}