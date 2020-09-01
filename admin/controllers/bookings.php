<?php

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\MVC\Controller\AdminController;
use Joomla\CMS\Factory;

/**
 * Controller handling operations on multiple booking records
 */
class U3ABookingControllerBookings extends AdminController
{
	/**
	 * Use the Booking model for all operations, rather than having a separate Bookings model 
	 */
	public function getModel($name = 'Booking', $prefix = 'U3ABookingModel', $config = array('ignore_request' => true))
	{
		$model = parent::getModel($name, $prefix, $config);
		return $model;
	}
	
	public function csvexport()
	{
		JSession::checkToken() or jexit(JText::_('JINVALID_TOKEN'));
		
		$app = Factory::getApplication(); 
		$input = $app->input; 
		$model = $this->getModel('booking');
		$eventModel = $this->getModel('event');
               
		// Get the current URI to set in redirects. 
		$currentUri = (string)JUri::getInstance();
       
	    // get the event whose bookings are to be exported - this should be set in the filter field
		$filters  = $input->get('filter', array(), 'array');
		if (!$filters['event_id'])
		{
			$this->setRedirect($currentUri, JText::_('COM_U3ABOOKING_SELECT_EVENT'), 'warning');
			return false;
		}
		
		$model->csvexport($filters['event_id']);
		$app->close();
	}
}