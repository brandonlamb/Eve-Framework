<?php
namespace Eve\Mvc;

class Router
{
    /**
     * @var array Holds all Route objects
     */
    protected $routes = array();

    /**
     * @var array Named routes, used for reverse routing.
     */
    protected $namedRoutes = array();

    /**
     * @var Route, the matched route
     */
    protected $route;

    /**
     * @var string The base REQUEST_URI. Gets prepended to all route url's.
     */
    protected $basePath;

    /**
     * @var string, default module name
     */
    protected $defaultModuleName = '';

    /**
     * @var string, default controller name
     */
    protected $defaultControllerName = 'index';

    /**
     * @var string, default action name
     */
    protected $defaultActionName = 'index';

    /**
     * @var string, routed module name
     */
    protected $moduleName;

    /**
     * @var string, routed controller name
     */
    protected $controllerName;

    /**
     * @var string, routed action name
     */
    protected $actionName;

    /**
     * @param string $basePath Set the base url - gets prepended to all route url's
     * @return Router
     */
    public function setBasePath($basePath)
    {
        $this->basePath = (string) $basePath;
        return $this;
    }

    /**
     * Route factory method
     *
     * Maps the given URL to the given target.
     * @param string $routeUrl string
     * @param mixed $target The target of this route. Can be anything. You'll have to provide your own method to turn
     * this into a filename, controller / action pair, etc..
     * @param array $args Array of optional arguments.
     * @return Router
     */
    public function map($routeUrl, $target = null, array $args = array())
    {
        $route = new Route();
        $route->setUrl($this->basePath . $routeUrl)->setTarget($target);

        if (isset($args['methods'])) {
            $methods = explode(',', $args['methods']);
            $route->setMethods($methods);
        }

        if (isset($args['filters'])) {
            $route->setFilters($args['filters']);
        }

        if (isset($args['name'])) {
            $route->setName($args['name']);
            if (!isset($this->namedRoutes[$route->getName()])) {
                $this->namedRoutes[$route->getName()] = $route;
            }
        }

        $this->routes[] = $route;
        return $this;
    }

    /**
     * Matches the current request against mapped routes
     * @return Route
     */
    public function matchCurrentRequest()
    {
        $requestMethod = (isset($_POST['_method']) && ($method = strtoupper($_POST['_method'])) && in_array($method, array('PUT', 'DELETE'))) ? $method : $_SERVER['REQUEST_METHOD'];
        $requestUrl = $_SERVER['REQUEST_URI'];

        // strip GET variables from URL
        if (($pos = strpos($requestUrl, '?')) !== false) {
            $requestUrl =  substr($requestUrl, 0, $pos);
        }

        return $this->match($requestUrl, $requestMethod);
    }

    /**
     * Match given request url and request method and see if a route has been defined for it
     * If so, return route's target
     * If called multiple times
     * @param string $requestUrl
     * @param string $requestMethod
     * @return mixed
     */
    public function match($requestUrl, $requestMethod = 'GET')
    {
        foreach ($this->routes as $route) {
            // compare server request method with route's allowed http methods
            if (!in_array($requestMethod, $route->getMethods())) {
                continue;
            }

            // check if request url matches route regex. if not, return false.
            if (!preg_match('@^' . $route->getRegex() . '$@i', $requestUrl, $matches)) {
                continue;
            }

            $params = array();

            if (preg_match_all('/:([\w-]+)/', $route->getUrl(), $argumentKeys)) {
                // grab array with matches
                $argumentKeys = $argumentKeys[1];

                // loop trough parameter names, store matching value in $params array
                foreach ($argumentKeys as $key => $name) {
                    if (isset($matches[$key + 1])) {
                        $params[$name] = $matches[$key + 1];
                    }
                }
            }

            $route->setParameters($params);
            $this->route = $route;
            return $this->route;
        }

        return false;
    }

    /**
     * Reverse route a named route
     *
     * @param string $routeName The name of the route to reverse route.
     * @param array $params Optional array of parameters to use in URL
     * @return string The url to the route
     */
    public function generate($routeName, array $params = array())
    {
        // Check if route exists
        if (!isset($this->namedRoutes[$routeName])) {
            throw new Exception("No route with the name $routeName has been found.");
        }

        $route = $this->namedRoutes[$routeName];
        $url = $route->getUrl();

        // replace route url with given parameters
        if ($params && preg_match_all('/:(\w+)/', $url, $paramKeys)) {
            // grab array with matches
            $paramKeys = $paramKeys[1];

            // loop trough parameter names, store matching value in $params array
            foreach ($paramKeys as $i => $key) {
                if (isset($params[$key])) {
                    $url = preg_replace('/:(\w+)/', $params[$key], $url, 1);
                }
            }
        }

        return $url;
    }

    /**
     * Set the default module name
     *
     * @param string $moduleName
     * @return Router
     */
    public function setDefaultModule($moduleName)
    {
        $this->defaultModuleName = (string) $moduleName;
        return $this;
    }

    /**
     * Set the default controller name
     *
     * @param string $controllerName
     * @return Router
     */
    public function setDefaultController($controllerName)
    {
        $this->defaultControllerName = (string) $controllerName;
        return $this;
    }

    /**
     * Set the default action name
     *
     * @param string $actionName
     * @return Router
     */
    public function setDefaultAction($actionName)
    {
        $this->defaultActionName = (string) $actionName;
        return $this;
    }

    /**
     * Get the routed module name
     *
     * @return string
     */
    public function getModuleName()
    {
        if (!$this->route instanceof Route) {
            return $this->defaultModuleName;
        }

        $target = $this->route->getTarget();
        if (isset($target['module'])) {
            return $target['module'];
        }

        $params = $this->route->getParameters();
        if (isset($params['module'])) {
            return $params['module'];
        }

        return $this->defaultModuleName;
    }

    /**
     * Get the routed controller name
     *
     * @return string
     */
    public function getControllerName()
    {
        if (!$this->route instanceof Route) {
            return $this->defaultControllerName;
        }

        $target = $this->route->getTarget();
        if (isset($target['controller'])) {
            return $target['controller'];
        }

        $params = $this->route->getParameters();
        if (isset($params['controller'])) {
            return $params['controller'];
        }

        return $this->controllerName;
    }

    /**
     * Get the routed action name
     *
     * @return string
     */
    public function getActionName()
    {
        if (!$this->route instanceof Route) {
            return $this->defaultActionName;
        }

        $target = $this->route->getTarget();
        if (isset($target['action'])) {
            return $target['action'];
        }

        $params = $this->route->getParameters();
        if (isset($params['action'])) {
            return $params['action'];
        }

        return $this->defaultActionName;
    }

    /**
     * Return the routed route
     *
     * @return Route
     */
    public function getRoute()
    {
        return null !== $this->route ? $this->route : new Route();
    }
}
