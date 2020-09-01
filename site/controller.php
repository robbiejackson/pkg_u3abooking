<?php
/**
 * U3ABooking Component Controller for displaying pages
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\MVC\Controller\BaseController;
use Joomla\CMS\Factory;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Uri\Uri;


class U3ABookingController extends BaseController
{
	public function display($cachable = false, $urlparams = array())
	{
		$document = Factory::getDocument();
		$app = Factory::getApplication();
		
		// For the view which displays the booking amend form, make sure that
		// the booking reference matches the record id and booking_ref_part
		$view = $app->input->get('view', '', 'string');
		$layout = $app->input->get('layout', '', 'string');
		if ($view == "booking" && $layout == "edit")
		{
			// find the booking record based on the id= URL param and check that the 
			// booking_ref_part within it matches the booking= URL param
			Table::addIncludePath(JPATH_ADMINISTRATOR . "/components/com_u3abooking/tables");
			$bookingTable = Table::getInstance('booking', 'U3ABookingTable', '');
			$id = $app->input->get('id', '', 'string');
			$result = $bookingTable->load($id);
			
			$booking_ref = $app->input->get('booking', '', 'string');
			
			if (!$result || (!isset($bookingTable->booking_ref_part)) || ($booking_ref != $id . $bookingTable->booking_ref_part))
			{
				// bad booking reference
				// redirect to the return URL if set, or home page if not
				$returnURL = $app->input->get('return', '', 'string');
				$redirectURL = $returnURL ? base64_decode($returnURL) : Uri::root();
				$this->setRedirect($redirectURL, JText::_('COM_U3ABOOKING_INVALID_BOOKING_REFERENCE'), 'warning');
				return false;
			}
		}
		parent::display($cachable, $urlparams);
	}
}