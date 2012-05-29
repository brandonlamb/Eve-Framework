<?php
/**
 * WebUserInterface interface is implemented by a {@link WebUser application component}.
 *
 * A user application component represents the identity information
 * for the current user.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @version $Id: interfaces.php 3515 2011-12-28 12:29:24Z mdomba $
 * @package system.base
 * @since 1.0
 */
namespace Eve\Auth;

interface WebUserInterface
{
	/**
	 * Returns a value that uniquely represents the identity.
	 *
	 * @return mixed a value that uniquely represents the identity (e.g. primary key value).
	 */
	public function getId();

	/**
	 * Returns the display name for the identity (e.g. username).
	 *
	 * @return string the display name for the identity.
	 */
	public function getName();

	/**
	 * Returns a value indicating whether the user is a guest (not authenticated).
	 *
	 * @return boolean whether the user is a guest (not authenticated)
	 */
	public function getIsGuest();

	/**
	 * Performs access check for this user.
	 *
	 * @param string $operation the name of the operation that need access check.
	 * @param array $params name-value pairs that would be passed to business rules associated
	 * with the tasks and roles assigned to the user.
	 * @return boolean whether the operations can be performed by this user.
	 */
	public function checkAccess($operation, $params = array());
}
