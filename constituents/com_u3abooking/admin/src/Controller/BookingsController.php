<?php
namespace Robbie\Component\U3ABooking\Administrator\Controller;

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\MVC\Controller\AdminController;
use Joomla\CMS\Factory;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Language\Text;

/**
 * Controller handling operations on multiple booking records
 */
class BookingsController extends AdminController
{
	/**
	 * Use the Booking model for all operations, rather than having a separate Bookings model 
	 */
	public function getModel($name = 'Booking', $prefix = 'administrator', $config = array('ignore_request' => true))
	{
		$model = parent::getModel($name, $prefix, $config);
		return $model;
	}
	
	public function csvexport()
	{
		$this->checkToken();
		
		$app = Factory::getApplication(); 
		$input = $app->input; 
		$model = $this->getModel('booking');
		$eventModel = $this->getModel('event');
               
		// Get the current URI to set in redirects. 
		$currentUri = (string)Uri::getInstance();
       
	    // get the event whose bookings are to be exported - this should be set in the filter field
		$filters  = $input->get('filter', array(), 'array');
		if (!$filters['event_id'])
		{
			$this->setRedirect($currentUri, Text::_('COM_U3ABOOKING_SELECT_EVENT'), 'warning');
			return false;
		}
		
		$model->csvexport($filters['event_id']);
		$app->close();
	}
}