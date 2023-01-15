<?php
/**
 * View for displaying the events and allowing the user to click on one to get to the booking form
 */
 
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\Factory;

class U3ABookingViewEvents extends HtmlView
{
	/**
	 * Display the event and form for booking
	 */
	
	function display($tpl = null)
	{
        $this->items = $this->get('Items');
		
		parent::display($tpl);
	}
}