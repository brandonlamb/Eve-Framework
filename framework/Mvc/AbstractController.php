<?php
/**
 * Eve Application Framework
 *
 * @author Phil Bayfield
 * @copyright 2010
 * @license Creative Commons Attribution-Share Alike 2.0 UK: England & Wales License
 * @package Eve
 * @version 0.1.0
 */
namespace Eve\Mvc;

abstract class AbstractController extends Component implements \Eve\ResourceInterface
{
	/**
	 * Request object
	 *
	 * @var Request
	 */
	protected $request;

	/**
	 * Dispatcher object
	 *
	 * @var Dispatcher
	 */
	protected $dispatcher;

	/**
	 * Layout view object
	 *
	 * @var View
	 */
	protected $view;

	/**
	 * Layout template file
	 *
	 * @var string
	 */
	const LAYOUT = null;

	/**
	 * Constructor
	 *
	 * @param Request $request
	 * @param string $controller
	 * @param string $action
	 * @param Exception $exception
	 */
	public function __construct($request, $dispatcher, $exception = null)
	{
		// Store reference to request and dispatcher objects in controller
		$this->request = $request;
		$this->dispatcher = $dispatcher;

		// Figure out whether to use class configured layout file or default from config
		if (null !== static::LAYOUT) {
			$layout = static::LAYOUT;
		} else {
			$layout = \Eve::app()
				->getComponent(static::RES_CONFIG)
				->modules[$request->getModule()]['default']['layout'];
		}

		// Create view object and set initial path, layout and view
		$this->view = new View($request);
		$this->view->setLayout($layout);

		// Set exception
		if (null !== $exception) {
			$this->view->set('exception', $exception);
		}
	}

	/**
	 * Return view renderer
	 *
	 * @return View
	 */
	public function getView()
	{
		return $this->view;
	}

	/**
	 * Set layout file
	 * Helper method to pass request through to view method
	 *
	 * @param string $file
	 * @return Controller
	 */
	public function setLayout($file)
	{
		$this->view->setLayout($file);
		return $this;
	}

	/**
	 * Get layout file
	 * Helper method to pass request through to view method
	 *
	 * @return string
	 */
	public function getLayout()
	{
		return $this->view->getLayout();
	}

	/**
	 * Extending controller classes can overwrite these methods
	 */
	public function init() {}

	public function beforeAction() {}

	public function afterAction()
	{
		// Pass rendered page to response
		\Eve::app()->getComponent(self::RES_RESPONSE)->setBody($this->view->render());
	}
}
