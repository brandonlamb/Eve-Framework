<?php
/**
 * @package Main
 */
namespace Main\Controller;

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
	}

	/**
	 * Exception action
	 *
	 * @return void
	 */
	public function actionException()
	{
		#$this->view->clear();
		\Eve::app()->response->clear();
	}
}
