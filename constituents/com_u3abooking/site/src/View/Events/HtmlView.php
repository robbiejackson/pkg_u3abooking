<?php
namespace Robbie\Component\U3ABooking\Site\View\Events;
/**
 * View for displaying the events and allowing the user to click on one to get to the booking form
 */
 
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Factory;

class HtmlView extends BaseHtmlView 
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