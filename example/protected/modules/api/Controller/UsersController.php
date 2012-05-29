<?php
/**
 * @package Main
 */
namespace Api\Controller;

// Namespace aliases
use Eve\Mvc as Mvc;

class UsersController extends Mvc\Controller
{
	/**
	 * Return customer data
	 *
	 * @return void
	 */
	public function actionData()
	{
		$this->view->testName = 'John Doe';
		$this->render(array(
			'status' => 'ok',
			'messages' => array(
				0 => 'One',
				1 => 'Two',
				2 => 'Three',
			),
		));
	}

	/**
	 * Exception action
	 *
	 * @return void
	 */
	public function actionException()
	{
		$this->actionNotFound();
	}
}
