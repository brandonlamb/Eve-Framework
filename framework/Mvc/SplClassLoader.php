<?php
/**
 * SplClassLoader implementation that implements the technical interoperability
 * standards for PHP 5.3 namespaces and class names.
 *
 * https://github.com/php-fig/fig-standards/blob/master/accepted/PSR-0.md
 *
 * Example usage:
 *
 *	 $classLoader = new \SplClassLoader();
 *
 *	 // Configure the SplClassLoader to act normally or silently
 *	 $classLoader->setMode(\SplClassLoader::MODE_NORMAL);
 *
 *	 // Add a namespace of classes
 *	 $classLoader->add('Doctrine', array(
 *		 '/path/to/doctrine-common', '/path/to/doctrine-dbal', '/path/to/doctrine-orm'
 *	 ));
 *
 *	 // Add a prefix
 *	 $classLoader->add('Swift', '/path/to/swift');
 *
 *	 // Add a prefix through PEAR1 convention, requiring include_path lookup
 *	 $classLoader->add('PEAR');
 *
 *	 // Allow to PHP use the include_path for file path lookup
 *	 $classLoader->setIncludePathLookup(true);
 *
 *	 // Possibility to change the default php file extension
 *	 $classLoader->setFileExtension('.php');
 *
 *	 // Register the autoloader, prepending it in the stack
 *	 $classLoader->register(true);
 *
 * @author Guilherme Blanco <guilhermeblanco@php.net>
 * @author Jonathan H. Wage <jonwage@gmail.com>
 * @author Roman S. Borschel <roman@code-factory.org>
 * @author Matthew Weier O'Phinney <matthew@zend.com>
 * @author Kris Wallsmith <kris.wallsmith@gmail.com>
 * @author Fabien Potencier <fabien.potencier@symfony-project.org>
 */
namespace Eve\Mvc;

// Namespace aliases
use \Eve\Mvc as Mvc;

require_once dirname(__FILE__) . '/SplAutoLoader.php';

class SplClassLoader implements Mvc\SplAutoloader
{
	/**
	 * @var string
	 */
	private $_fileExtension = '.php';

	/**
	 * @var boolean
	 */
	private $_includePathLookup = false;

	/**
	 * @var array
	 */
	private $_resources = array();

	/**
	 * @var integer
	 */
	private $_mode = self::MODE_NORMAL;

	/**
	 * Class => path map, found via:
	 * cd Eve ; find . *.php -type f | grep -v HtmlPur | grep -v phpmailer
	 *
	 * @var array
	 */
	private static $_class = array(
		'Eve\Util\File'					=> '/Eve/framework/Util/File.php',
		'Eve\Util\Strings'				=> '/Eve/framework/Util/Strings.php',
		'Eve\Util\Mobile'				=> '/Eve/framework/Util/Mobile.php',
		'Eve\Util\Arrays'				=> '/Eve/framework/Util/Arrays.php',
		'Eve\Util\Map'					=> '/Eve/framework/Util/Map.php',
		'Eve\Util\Date'					=> '/Eve/framework/Util/Date.php',
		'Eve\Util\Input'				=> '/Eve/framework/Util/Input.php',
		'Eve\Util\Upload'				=> '/Eve/framework/Util/Upload.php',
		'Eve\Filter\Strings'			=> '/Eve/framework/Filter/Strings.php',
		'Eve\Session\Apc'				=> '/Eve/framework/Session/Apc.php',
		'Eve\Session\File'				=> '/Eve/framework/Session/File.php',
		'Eve\Session\Db'				=> '/Eve/framework/Session/Db.php',
		'Eve\Session\DriverInterface'	=> '/Eve/framework/Session/DriverInterface.php',
		'Eve\Session\Memcache'			=> '/Eve/framework/Session/Memcache.php',
		'Eve\Session\Cookie'			=> '/Eve/framework/Session/Cookie.php',
		'Eve\Session\SweeperInterface'	=> '/Eve/framework/Session/SweeperInterface.php',
		'Eve\Cache\File'				=> '/Eve/framework/Cache/File.php',
		'Eve\Cache\File\Exception'		=> '/Eve/framework/Cache/File/Exception.php',
		'Eve\Cache\Memcache\Exception'	=> '/Eve/framework/Cache/Memcache/Exception.php',
		'Eve\Cache\Memcache'			=> '/Eve/framework/Cache/Memcache.php',
		'Eve\Curl\Json'					=> '/Eve/framework/Curl/Json.php',
		'Eve\Database\Mysql'			=> '/Eve/framework/Database/Mysql.php',
		'Eve\Database\Pdo'				=> '/Eve/framework/Database/Pdo.php',
		'Eve\Database\Exception'		=> '/Eve/framework/Database/Exception.php',
		'Eve\Mvc\ControllerException'	=> '/Eve/framework/Mvc/ControllerException.php',
		'Eve\Mvc\Component'				=> '/Eve/framework/Mvc/Component.php',
		'Eve\Mvc\AbstractController'	=> '/Eve/framework/Mvc/AbstractController.php',
		'Eve\Mvc\Autoloader'			=> '/Eve/framework/Mvc/Autoloader.php',
		'Eve\Mvc\SplClassLoader'		=> '/Eve/framework/Mvc/SplClassLoader.php',
		'Eve\Mvc\DispatcherException'	=> '/Eve/framework/Mvc/DispatcherException.php',
		'Eve\Mvc\Event'					=> '/Eve/framework/Mvc/Event.php',
		'Eve\Mvc\View'					=> '/Eve/framework/Mvc/View.php',
		'Eve\Mvc\Request'				=> '/Eve/framework/Mvc/Request.php',
		'Eve\Mvc\Request\Method'		=> '/Eve/framework/Mvc/Request/Method.php',
		'Eve\Mvc\Config'				=> '/Eve/framework/Mvc/Config.php',
		'Eve\Mvc\ViewException'			=> '/Eve/framework/Mvc/ViewException.php',
		'Eve\Mvc\Error'					=> '/Eve/framework/Mvc/Error.php',
		'Eve\Mvc\Response'				=> '/Eve/framework/Mvc/Response.php',
		'Eve\Mvc\Dispatcher'			=> '/Eve/framework/Mvc/Dispatcher.php',
		'Eve\Mvc\Router\AbstractRouter'	=> '/Eve/framework/Mvc/Router/AbstractRouter.php',
		'Eve\Mvc\Router\Simple'			=> '/Eve/framework/Mvc/Router/Simple.php',
		'Eve\Mvc\Router\RouterInterface'=> '/Eve/framework/Mvc/Router/RouterInterface.php',
		'Eve\Mvc\RequestException'		=> '/Eve/framework/Mvc/RequestException.php',
		'Eve\Mvc\SplAutoLoader'			=> '/Eve/framework/Mvc/SplAutoLoader.php',
		'Eve\Mvc\Exception'				=> '/Eve/framework/Mvc/Exception.php',
		'Eve\Mvc\ErrorException'		=> '/Eve/framework/Mvc/ErrorException.php',
		'Eve\Benchmark'					=> '/Eve/framework/Benchmark.php',
		'Eve\Crypter'					=> '/Eve/framework/Crypter.php',
		'Eve\Database'					=> '/Eve/framework/Database.php',
		'Eve\EveBase'					=> '/Eve/framework/EveBase.php',
		'Eve\Eve'						=> '/Eve/framework/Eve.php',
		'Eve\Exception'					=> '/Eve/framework/Exception.php',
		'Eve\Filter'					=> '/Eve/framework/Filter.php',
		'Eve\Flash'						=> '/Eve/framework/Flash.php',
		'Eve\Logger'					=> '/Eve/framework/Logger.php',
		'Eve\Mail'						=> '/Eve/framework/Mail.php',
		'Eve\PasswordHash'				=> '/Eve/framework/PasswordHash.php',
		'Eve\Session'					=> '/Eve/framework/Session.php',
		'Eve\Validate'					=> '/Eve/framework/Validate.php',
	);

	/**
	 * {@inheritdoc}
	 */
	public function setMode($mode)
	{
		if ($mode & self::MODE_SILENT && $mode & self::MODE_NORMAL) {
			throw new \InvalidArgumentException(
				sprintf('Cannot have %s working normally and silently at the same time!', __CLASS__)
			);
		}
		$this->_mode = $mode;
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
		$this->_fileExtension = $fileExtension;
		return $this;
	}

	/**
	 * Retrieve the file extension of resource files in the path of this class loader.
	 *
	 * @return string
	 */
	public function getFileExtension()
	{
		return $this->_fileExtension;
	}

	/**
	 * Turns on searching the include for class files. Allows easy loading installed PEAR packages.
	 *
	 * @param boolean $includePathLookup
	 * @return SplClassLoader
	 */
	public function setIncludePathLookup($includePathLookup)
	{
		$this->_includePathLookup = $includePathLookup;
		return $this;
	}

	/**
	 * Gets the base include path for all class files in the namespace of this class loader.
	 *
	 * @return boolean
	 */
	public function getIncludePathLookup()
	{
		return $this->_includePathLookup;
	}

	/**
	 * {@inheritdoc}
	 */
	public function register($prepend = false)
	{
		spl_autoload_register(array($this, 'load'), true, $prepend);
	}

	/**
	 * {@inheritdoc}
	 */
	public function unregister()
	{
		spl_autoload_unregister(array($this, 'load'));
	}

	/**
	 * {@inheritdoc}
	 */
	public function add($resource, $resourcePath = null)
	{
		$this->_resources[$resource] = (array) $resourcePath;
	}

	/**
	 * {@inheritdoc}
	 */
	public function load($resourceName)
	{
		if (isset(static::$_class[$resourceName])) {
#			d(\Eve::app()->getComponent('config')->get('vendorsPath') . static::$_class[$resourceName]);
			require_once\Eve::app()->getComponent('config')->get('vendorsPath') . static::$_class[$resourceName];
			return true;
		}

		$resourceAbsolutePath = $this->getResourceAbsolutePath($resourceName);

		switch ($this->_mode) {
			case self::MODE_SILENT:
				if ($resourceAbsolutePath === false) {
					return false;
				}
				require_once$resourceAbsolutePath;
				break;

			case self::MODE_DEBUG:
				if ($resourceAbsolutePath === false) {
					throw new \RuntimeException(
						sprintf('Autoloader unable to find path to "%s"', $resourceName)
					);
				}
				require_once$resourceAbsolutePath;
				break;

			case self::MODE_NORMAL:
			default:
				if ($resourceAbsolutePath === false) {
					return false;
				}
				require_once$resourceAbsolutePath;
				break;
		}

		if ($this->_mode & self::MODE_DEBUG && ! $this->isResourceDeclared($resourceName)) {
			throw new \RuntimeException(
				sprintf('Autoloader expected resource "%s" to be declared in file "%s".', $resourceName, $resourceAbsolutePath)
			);
		}

		return true;
	}

	/**
	 * Transform resource name into its absolute resource path representation.
	 *
	 * @param string $resourceName
	 * @return string|bool Resource absolute path.
	 */
	private function getResourceAbsolutePath($resourceName)
	{
		foreach ($this->_resources as $resource => $resourcesPath) {
			if (strpos($resourceName, $resource) !== 0) {
				continue;
			}

			foreach ($resourcesPath as $resourcePath) {
				$resourceRelativePath = $this->getResourceRelativePath($resourceName);
				$resourceAbsolutePath = $resourcePath . DIRECTORY_SEPARATOR . $resourceRelativePath;

				if (($resourceAbsolutePath = stream_resolve_include_path($resourceAbsolutePath)) !== false) {
					return $resourceAbsolutePath;
				}
			}
		}

		if ($this->_includePathLookup && ($resourceAbsolutePath = stream_resolve_include_path($resourceRelativePath)) !== false) {
			return $resourceAbsolutePath;
		}

		return false;
	}

	/**
	 * Transform resource name into its relative resource path representation.
	 *
	 * @param string $resourceName
	 * @return string Resource relative path.
	 */
	private function getResourceRelativePath($resourceName)
	{
		// We always work with FQCN in this context
		$resourceName = ltrim($resourceName, '\\');
		$resourcePath = '';

		if (($lastNamespacePosition = strpos($resourceName, '\\')) !== false) {
			// Namespaced resource name
			$resourceNamespace = substr($resourceName, 0, $lastNamespacePosition);
			$resourceName = substr($resourceName, $lastNamespacePosition + 1);
			$resourcePath =  str_replace('\\', DIRECTORY_SEPARATOR, $resourceName);
		}

		return str_replace('_', DIRECTORY_SEPARATOR, $resourcePath) . $this->_fileExtension;
	}

	/**
	 * Check if resource is declared in user space.
	 *
	 * @param string $resourceName
	 * @return boolean
	 */
	private function isResourceDeclared($resourceName)
	{
		return class_exists($resourceName, false)
			|| interface_exists($resourceName, false)
			|| (function_exists('trait_exists') && trait_exists($resourceName, false));
	}
}
