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
		'Eve\Util\File'					=> '/Eve/Util/File.php',
		'Eve\Util\Strings'				=> '/Eve/Util/Strings.php',
		'Eve\Util\Mobile'				=> '/Eve/Util/Mobile.php',
		'Eve\Util\Arrays'				=> '/Eve/Util/Arrays.php',
		'Eve\Util\Map'					=> '/Eve/Util/Map.php',
		'Eve\Util\Date'					=> '/Eve/Util/Date.php',
		'Eve\Util\Input'				=> '/Eve/Util/Input.php',
		'Eve\Util\Upload'				=> '/Eve/Util/Upload.php',
		'Eve\Filter\Strings'			=> '/Eve/Filter/Strings.php',
		'Eve\Session\Apc'				=> '/Eve/Session/Apc.php',
		'Eve\Session\File'				=> '/Eve/Session/File.php',
		'Eve\Session\Db'				=> '/Eve/Session/Db.php',
		'Eve\Session\DriverInterface'	=> '/Eve/Session/DriverInterface.php',
		'Eve\Session\Memcache'			=> '/Eve/Session/Memcache.php',
		'Eve\Session\Cookie'			=> '/Eve/Session/Cookie.php',
		'Eve\Session\SweeperInterface'	=> '/Eve/Session/SweeperInterface.php',
		'Eve\Cache\File'				=> '/Eve/Cache/File.php',
		'Eve\Cache\File\Exception'		=> '/Eve/Cache/File/Exception.php',
		'Eve\Cache\Memcache\Exception'	=> '/Eve/Cache/Memcache/Exception.php',
		'Eve\Cache\Memcache'			=> '/Eve/Cache/Memcache.php',
		'Eve\Curl\Json'					=> '/Eve/Curl/Json.php',
		'Eve\Database\Mysql'			=> '/Eve/Database/Mysql.php',
		'Eve\Database\Pdo'				=> '/Eve/Database/Pdo.php',
		'Eve\Database\Exception'		=> '/Eve/Database/Exception.php',
		'Eve\Mvc\ControllerException'	=> '/Eve/Mvc/ControllerException.php',
		'Eve\Mvc\Component'				=> '/Eve/Mvc/Component.php',
		'Eve\Mvc\AbstractController'	=> '/Eve/Mvc/AbstractController.php',
		'Eve\Mvc\Autoloader'			=> '/Eve/Mvc/Autoloader.php',
		'Eve\Mvc\SplClassLoader'		=> '/Eve/Mvc/SplClassLoader.php',
		'Eve\Mvc\DispatcherException'	=> '/Eve/Mvc/DispatcherException.php',
		'Eve\Mvc\Event'					=> '/Eve/Mvc/Event.php',
		'Eve\Mvc\View'					=> '/Eve/Mvc/View.php',
		'Eve\Mvc\Request'				=> '/Eve/Mvc/Request.php',
		'Eve\Mvc\Request\Method'		=> '/Eve/Mvc/Request/Method.php',
		'Eve\Mvc\Config'				=> '/Eve/Mvc/Config.php',
		'Eve\Mvc\ViewException'			=> '/Eve/Mvc/ViewException.php',
		'Eve\Mvc\Error'					=> '/Eve/Mvc/Error.php',
		'Eve\Mvc\Response'				=> '/Eve/Mvc/Response.php',
		'Eve\Mvc\Dispatcher'			=> '/Eve/Mvc/Dispatcher.php',
		'Eve\Mvc\Router\AbstractRouter'	=> '/Eve/Mvc/Router/AbstractRouter.php',
		'Eve\Mvc\Router\Simple'			=> '/Eve/Mvc/Router/Simple.php',
		'Eve\Mvc\Router\RouterInterface'=> '/Eve/Mvc/Router/RouterInterface.php',
		'Eve\Mvc\RequestException'		=> '/Eve/Mvc/RequestException.php',
		'Eve\Mvc\SplAutoLoader'			=> '/Eve/Mvc/SplAutoLoader.php',
		'Eve\Mvc\Exception'				=> '/Eve/Mvc/Exception.php',
		'Eve\Mvc\ErrorException'		=> '/Eve/Mvc/ErrorException.php',
		'Eve\Benchmark'					=> '/Eve/Benchmark.php',
		'Eve\Crypter'					=> '/Eve/Crypter.php',
		'Eve\Database'					=> '/Eve/Database.php',
		'Eve\EveBase'					=> '/Eve/EveBase.php',
		'Eve\Eve'						=> '/Eve/Eve.php',
		'Eve\Exception'					=> '/Eve/Exception.php',
		'Eve\Filter'					=> '/Eve/Filter.php',
		'Eve\Flash'						=> '/Eve/Flash.php',
		'Eve\Logger'					=> '/Eve/Logger.php',
		'Eve\Mail'						=> '/Eve/Mail.php',
		'Eve\PasswordHash'				=> '/Eve/PasswordHash.php',
		'Eve\Session'					=> '/Eve/Session.php',
		'Eve\Validate'					=> '/Eve/Validate.php',
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
	}

	/**
	 * Define the file extension of resource files in the path of this class loader.
	 *
	 * @param string $fileExtension
	 */
	public function setFileExtension($fileExtension)
	{
		$this->_fileExtension = $fileExtension;
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
	 */
	public function setIncludePathLookup($includePathLookup)
	{
		$this->_includePathLookup = $includePathLookup;
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
			require \Eve::app()->getComponent('config')->get('vendorsPath') . static::$_class[$resourceName];
			return true;
		}

		$resourceAbsolutePath = $this->getResourceAbsolutePath($resourceName);

		switch ($this->_mode) {
			case self::MODE_SILENT:
echo "LOAD: $resourceName / $resourceAbsolutePath\n";
				if ($resourceAbsolutePath === false) {
					return false;
				}
				require $resourceAbsolutePath;
				break;

			case self::MODE_DEBUG:
				if ($resourceAbsolutePath === false) {
					throw new \RuntimeException(
						sprintf('Autoloader unable to find path to "%s"', $resourceName)
					);
				}
				require $resourceAbsolutePath;
				break;

			case self::MODE_NORMAL:
			default:

				require $resourceAbsolutePath;
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
	 * @return string Resource absolute path.
	 */
	private function getResourceAbsolutePath($resourceName)
	{
		$resourceRelativePath = $this->getResourceRelativePath($resourceName);

		foreach ($this->_resources as $resource => $resourcesPath) {
			if (strpos($resourceName, $resource) !== 0) {
				continue;
			}

			foreach ($resourcesPath as $resourcePath) {
				$resourceAbsolutePath = $resourcePath . DIRECTORY_SEPARATOR . $resourceRelativePath;

				if (is_file($resourceAbsolutePath)) {
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

		if (($lastNamespacePosition = strrpos($resourceName, '\\')) !== false) {
			// Namespaced resource name
			$resourceNamespace = substr($resourceName, 0, $lastNamespacePosition);
			$resourceName = substr($resourceName, $lastNamespacePosition + 1);
			$resourcePath =  str_replace('\\', DIRECTORY_SEPARATOR, $resourceNamespace) . DIRECTORY_SEPARATOR;
		}

		return $resourcePath . str_replace('_', DIRECTORY_SEPARATOR, $resourceName) . $this->_fileExtension;
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
