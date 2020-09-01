<?php
/**
 * View for displaying an event and allowing booking against it
 */
 
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Log\Log;

class U3ABookingViewBookingConfirmation extends HtmlView
{
	/**
	 * Display the booking confirmation
	 */
	
	function display($tpl = null)
	{
		$eventModel = $this->getModel('event');
		$this->event = $eventModel->getItem();
		
		$bookingModel = $this->getModel('booking');
		$bookingId = $bookingModel->getState('booking.id');
		$this->booking = $bookingModel->getItem($bookingId);

		$user = Factory::getUser();
		$app = Factory::getApplication();

		// handle if event or booking not found - there's a bug somewhere
		if (!$this->event)
		{
			$app->enqueueMessage(JText::_('COM_U3ABOOKING_NO_EVENT'), 'warning');
			Log::add("No event found in booking confirmation", Log::ERROR, 'u3a-error');
		}
		if (!$this->booking)
		{
			$app->enqueueMessage(JText::_('COM_U3ABOOKING_NO_BOOKING'), 'warning');
			Log::add("No booking found in booking confirmation", Log::ERROR, 'u3a-error');
		}
		
		parent::display($tpl);
	}
}