<?php
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\MVC\Controller\AdminController;

/**
 * This is the controller which handles POST requests associated with actions in the Admin Events form
 * (The view displaying the Events doesn't use this controller)
 */

class U3ABookingControllerEvents extends AdminController
{
	/**
	 * We use the Event model for handling all POST actions in the Events form
	 * 
	 */
	public function getModel($name = 'Event', $prefix = 'U3ABookingModel', $config = array('ignore_request' => true))
	{
		$model = parent::getModel($name, $prefix, $config);
		return $model;
	}
}