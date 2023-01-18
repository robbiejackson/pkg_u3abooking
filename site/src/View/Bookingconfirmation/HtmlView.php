<?php
namespace Robbie\Component\U3ABooking\Site\View\Bookingconfirmation;
/**
 * View for displaying an event and allowing booking against it
 */
 
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Factory;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Language\Text;

class HtmlView extends BaseHtmlView 
{
	/**
	 * Display the booking confirmation
	 */
	
	function display($tpl = null)
	{
		$this->event = $this->getModel('event')->getItem();
		
		$bookingId = $this->getModel('booking')->getState('booking.id');
		$this->booking = $this->getModel('booking')->getItem($bookingId);

		$app = Factory::getApplication();
        $user = $app->getIdentity();

		// handle if event or booking not found - there's a bug somewhere
		if (!$this->event)
		{
			$app->enqueueMessage(Text::_('COM_U3ABOOKING_NO_EVENT'), 'warning');
			Log::add("No event found in booking confirmation", Log::ERROR, 'u3a-error');
		}
		if (!$this->booking)
		{
			$app->enqueueMessage(Text::_('COM_U3ABOOKING_NO_BOOKING'), 'warning');
			Log::add("No booking found in booking confirmation", Log::ERROR, 'u3a-error');
		}
		
		parent::display($tpl);
	}
}