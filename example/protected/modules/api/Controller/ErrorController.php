<?php
/**
 * @package Main
 */
namespace Api\Controller;

// Namespace aliases
use Eve\Mvc as Mvc;

class ErrorController extends Mvc\Controller
{
	/**
	 * No found action
	 *
	 * @return void
	 */
	public function actionNotFound()
	{
		\Eve::app()->response->status(404);
		$this->view->set(array(
			'status' => 'error',
			'message' => '404 Not Found',
		));
	}

	/**
	 * Exception action
	 *
	 * @return void
	 */
	public function actionException()
	{
		$error = $this->_exception instanceof \Exception ? $this->_exception->getMessage() : null;
		$this->view->set(array(
			'status' => 'error',
			'message' => $error,
		));
	}
}
