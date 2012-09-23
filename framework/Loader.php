<?php
namespace Eve;

use Eve\Mvc as Mvc;

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
	 * @var string
	 */
	protected $fileExtension = '.php';

	/**
	 * @var boolean
	 */
	protected $includePathLookup = false;

	/**
	 * @var array Class => Path entries
	 */
	protected $classPaths = array();

	/**
	 * @var array Namespace => Path entries
	 */
	protected $namespacePaths = array();

	/**
	 * @var array Extra directories to search for not found classes
	 */
	protected $dirPaths = array();

	/**
	 * @var integer
	 */
	protected $mode = self::MODE_NORMAL;

	/**
	 * Define the autoloader work mode.
	 *
	 * @param integer $mode Autoloader work mode.
	 * @return SplAutoLoader
	 */
	public function setMode($mode)
	{
		if ($mode & self::MODE_SILENT && $mode & self::MODE_NORMAL) {
			throw new \InvalidArgumentException(
				sprintf('Cannot have %s working normally and silently at the same time!', __CLASS__)
			);
		}
		$this->mode = $mode;
		return $this;
	}

	/**
	 * Define the file extension of resource files in the path of this class loader.
	 *
	 * @param string $fileExtension
	 * @return SplClassLoader
	 */
	public function setFileExtension($fileExtension)
	{
		$this->fileExtension = (string) $fileExtension;
		return $this;
	}

	/**
	 * Retrieve the file extension of resource files in the path of this class loader.
	 *
	 * @return string
	 */
	public function getFileExtension()
	{
		return $this->fileExtension;
	}

	/**
	 * Turns on searching the include for class files. Allows easy loading installed PEAR packages.
	 *
	 * @param boolean $includePathLookup
	 * @return SplClassLoader
	 */
	public function setIncludePathLookup($includePathLookup)
	{
		$this->includePathLookup = (bool) $includePathLookup;
		return $this;
	}

	/**
	 * Gets the base include path for all class files in the namespace of this class loader.
	 *
	 * @return boolean
	 */
	public function getIncludePathLookup()
	{
		return $this->includePathLookup;
	}

	/**
	 * Register this as an autoloader instance.
	 *
	 * @param boolean Whether to prepend the autoloader or not in autoloader's list.
	 */
	public function register($prepend = false)
	{
		spl_autoload_register(array($this, 'load'), true, $prepend);
	}

	/**
	 * Unregister this autoloader instance.
	 */
	public function unregister()
	{
		spl_autoload_unregister(array($this, 'load'));
	}

	/**
	 * Register class paths
	 *
	 * @param array $paths
	 * @return Loader
	 */
	public function registerClasses(array $paths)
	{
		$this->classPaths = $paths;
		return $this;
	}

	/**
	 * Register namespace paths
	 *
	 * @param array $paths
	 * @return Loader
	 */
	public function registerNamespaces(array $paths)
	{
		$this->namespacePaths = $paths;
		return $this;
	}

	/**
	 * Register directory paths
	 *
	 * @param array $paths
	 * @return Loader
	 */
	public function registerDirs(array $paths)
	{
		$this->dirPaths = $paths;
		return $this;
	}

	/**
	 * Load a resource through provided resource name.
	 *
	 * @param string $className Class name to be loaded.
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

		// Get relative path/file for class name
		$relativePath = $this->getRelativePath($className);

		// Check registered namespace paths
		foreach ($this->namespacePaths as $resource => $resourcePaths) {
			if (strpos($className, $resource) !== 0) {
				continue;
			}

			foreach ($resourcePaths as $resourcePath) {
				if (($absolutePath = stream_resolve_include_path(rtrim($resourcePath, '/') . DIRECTORY_SEPARATOR . $relativePath)) !== false) {
					return $absolutePath;
				}
			}
		}

		// Check registered directory paths
		foreach ($this->dirPaths as $resourcePath) {
			if (($absolutePath = stream_resolve_include_path(rtrim($resourcePath, '/') . DIRECTORY_SEPARATOR . $relativePath)) !== false) {
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
	 * @param string $className
	 * @return string Resource relative path.
	 */
	protected function getRelativePath($className)
	{
		// We always work with FQCN in this context
		$className = ltrim($className, '\\');

		if (($lastNamespacePosition = strpos($className, '\\')) !== false) {
			// Namespaced resource name
#			$classNamespace = substr($className, 0, $lastNamespacePosition);
			$className = substr($className, $lastNamespacePosition + 1);
		}

		return str_replace(array('\\', '_'), DIRECTORY_SEPARATOR, $className) . $this->fileExtension;
	}

	/**
	 * Check if resource is declared in user space.
	 *
	 * @param string $className
	 * @return boolean
	 */
	protected function isResourceDeclared($className)
	{
		return class_exists($className, false)
			|| interface_exists($className, false)
			|| (function_exists('trait_exists') && trait_exists($className, false));
	}
}
