<?php
/**
 * @package Main
 */
namespace Main\Controller;

// Namespace aliases
use \Eve\Mvc as Mvc;

class IndexController extends Mvc\Controller
{
	/**
	 * Index action
	 *
	 * @param string $param1
	 * @return void
	 */
	public function actionIndex($name = null)
	{
		$this->view->name = $name;
	}

	public function actionJson()
	{
		$app = \Eve::app();
		$this->view->set(array(
			'status' => 'ok',
			'data' => array(
				'classes' => array(
					'one',
					'two',
				),
			),
		));
	}
}
