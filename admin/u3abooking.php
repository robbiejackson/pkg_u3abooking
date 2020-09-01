<?php

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Controller\BaseController;

// Standard admin entry point file functionality

if (!Factory::getUser()->authorise('core.manage', 'com_u3abooking'))
{
	throw new Exception(JText::_('JERROR_ALERTNOAUTHOR'));
}

$controller = BaseController::getInstance('U3ABooking');

$input = Factory::getApplication()->input;
$controller->execute($input->getCmd('task'));

$controller->redirect();