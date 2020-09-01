<?php
/**
 * main site entry point file for com_u3abooking
 */
 
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Factory;

$controller = BaseController::getInstance('U3ABooking');
 
$input = Factory::getApplication()->input;
$controller->execute($input->getCmd('task'));
 
$controller->redirect();