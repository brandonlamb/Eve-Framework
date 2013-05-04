<?php
namespace Eve\Autoload;

class Loader
{
    /**
     * Defines autoloader to work silently if resource is not found.
     *
     * @const int
     */
    const MODE_SILENT = 0;

    /**
     * Defines autoloader to work normally (requiring an un-existent resource).
     *
     * @const int
     */
    const MODE_NORMAL = 1;

    /**
     * Defines autoloader to work in debug mode, loading file and validating requested resource.
     *
     * @const int
     */
    const MODE_DEBUG = 2;

    /**
     * @var bool
     */
    protected $includePathLookup = false;

    /**
     * @var array Class => Path entries
     */
    protected $classPaths = [];

    /**
     * @var array Namespace => Path entries
     */
    protected $namespacePaths = [];

    /**
     * @var array Extra directories to search for not found classes
     */
    protected $dirPaths = [];

    /**
     * @var integer
     */
    protected $mode = self::MODE_NORMAL;

    /**
     * Define the autoloader work mode.
     *
     * @param integer $mode Autoloader work mode.
     * @return Loader
     */
    public function setMode($mode)
    {
        $mode = (int) $mode;
        if ($mode & self::MODE_SILENT && $mode & self::MODE_NORMAL) {
            throw new \InvalidArgumentException(
                sprintf('Cannot have %s working normally and silently at the same time!', __CLASS__)
            );
        }
        $this->mode = $mode;
        return $this;
    }

    /**
     * Turns on searching the include for class files. Allows easy loading installed PEAR packages.
     *
     * @param bool $includePathLookup
     * @return Loader
     */
    public function setIncludePathLookup($includePathLookup)
    {
        $this->includePathLookup = (bool) $includePathLookup;
        return $this;
    }

    /**
     * Gets the base include path for all class files in the namespace of this class loader.
     *
     * @return bool
     */
    public function getIncludePathLookup()
    {
        return (bool) $this->includePathLookup;
    }

    /**
     * Register this as an autoloader instance.
     *
     * @param bool Whether to prepend the autoloader or not in autoloader's list.
     * @return Loader
     */
    public function register($prepend = false)
    {
        spl_autoload_register([$this, 'load'], true, (bool) $prepend);
        return $this;
    }

    /**
     * Unregister this autoloader instance.
     * @return Loader
     */
    public function unregister()
    {
        spl_autoload_unregister([$this, 'load']);
        return $this;
    }

    /**
     * Set all class paths
     *
     * @param array $paths
     * @return Loader
     */
    public function setClasses(array $paths)
    {
        $this->classPaths = $paths;
        return $this;
    }

    /**
     *
     * Adds file paths for class names to the existing exact mappings.
     *
     * @param array $paths An array of class-to-file mappings where the key
     * is the class name and the value is the file path.
     * @return Loader
     *
     */
    public function addClasses(array $paths)
    {
        $this->classPaths = array_merge($this->classPaths, $paths);
        return $this;
    }

    /**
     * Set all namespace paths
     *
     * @param array $paths
     * @return Loader
     */
    public function setNamespaces(array $paths)
    {
        $this->namespacePaths = $paths;
        return $this;
    }

    /**
     * Add namespace paths
     *
     * @param array $paths
     * @return Loader
     */
    public function addNamespaces(array $paths)
    {
        $this->namespacePaths = array_merge($this->namespacePaths, $paths);
        return $this;
    }

    /**
     * Set all directory paths
     *
     * @param array $paths
     * @return Loader
     */
    public function setDirs(array $paths)
    {
        $this->dirPaths = $paths;
        return $this;
    }

    /**
     * Add directory paths
     *
     * @param array $paths
     * @return Loader
     */
    public function addDirs(array $paths)
    {
        $this->dirPaths = array_merge($this->dirPaths, $paths);
        return $this;
    }

    /**
     * Load a resource through provided resource name.
     *
     * @param  string $className Class name to be loaded.
     * @return bool
     */
    public function load($className)
    {
        $resourceAbsolutePath = $this->getAbsolutePath($className);

        switch ($this->mode) {
            case self::MODE_SILENT:
                if ($resourceAbsolutePath === false) {
                    return false;
                }
                break;

            case self::MODE_DEBUG:
                if ($resourceAbsolutePath === false) {
                    throw new \RuntimeException(
                        sprintf('Autoloader unable to find path to "%s"', $className)
                    );
                }
                break;

            case self::MODE_NORMAL:
            default:
                if ($resourceAbsolutePath === false) {
                    return false;
                }
                break;
        }

        require_once $resourceAbsolutePath;

        // Extra catch in case the file was loaded but the class was not in the file
        if ($this->mode & self::MODE_DEBUG && ! $this->isResourceDeclared($className)) {
            throw new \RuntimeException(
                sprintf('Autoloader expected resource "%s" to be declared in file "%s".', $className, $resourceAbsolutePath)
            );
        }

        return true;
    }

    /**
     * Transform resource name into its absolute resource path representation.
     *
     * @param string $className
     * @return string|bool Resource absolute path.
     */
    protected function getAbsolutePath($className)
    {
        // Check registered classes, if the path resolves then return it, otherwise reset relative path variable
        if (isset($this->classPaths[$className]) && ($absolutePath = stream_resolve_include_path($this->classPaths[$className])) !== false) {
            return $absolutePath;
        }

        // Get relative path/file for class name with namespace stripped
        $relativePath = $this->getRelativePath($className, true);

        // Check registered namespace paths
        foreach ($this->namespacePaths as $resource => $resourcePaths) {
            if (strpos($className, $resource) !== 0) {
                continue;
            }

            // $resourcePaths should be array
            !is_array($resourcePaths) && $resourcePaths = [(string) $resourcePaths];

            foreach ($resourcePaths as $resourcePath) {
                if (($absolutePath = stream_resolve_include_path(rtrim($resourcePath, '/') . DIRECTORY_SEPARATOR . $relativePath)) !== false) {
                    return $absolutePath;
                }
            }
        }

        // Get relative path/file for class name
        $relativePath = $this->getRelativePath($className);

        // Check registered directory paths
        foreach ($this->dirPaths as $resourcePath) {
            $absolutePath = stream_resolve_include_path(rtrim($resourcePath, '/') . DIRECTORY_SEPARATOR . $relativePath);

            if ($absolutePath !== false) {
                return $absolutePath;
            }
        }

        // Try searching include path if includePathLookup is set to true
        if ($this->includePathLookup && ($absolutePath = stream_resolve_include_path($relativePath)) !== false) {
            return $resourceAbsolutePath;
        }

        return false;
    }

    /**
     * Transform resource name into its relative resource path representation.
     *
     * @param  string $className
     * @param bool $stripNs, true if stripping leading namespace
     * @return string Resource relative path.
     */
    protected function getRelativePath($className, $stripNs = false)
    {
        // We always work with FQCN in this context
        $className = ltrim($className, '\\');

        if ($stripNs && ($lastNamespacePosition = strpos($className, '\\')) !== false) {
            // Namespaced resource name
            $className = substr($className, $lastNamespacePosition + 1);
        }

        return str_replace(['\\', '_'], DIRECTORY_SEPARATOR, $className) . '.php';
    }

    /**
     * Check if resource is declared in user space.
     *
     * @param string $className
     * @return bool
     */
    protected function isResourceDeclared($className)
    {
        return class_exists($className, false)
            || interface_exists($className, false)
            || (function_exists('trait_exists') && trait_exists($className, false));
    }
}
