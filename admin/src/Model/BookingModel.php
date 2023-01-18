<?php
namespace Robbie\Component\U3ABooking\Administrator\Model;

defined('_JEXEC') or die('Restricted access');

use Joomla\Registry\Registry;
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Factory;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Log\Log;
use Robbie\Component\U3ABooking\Administrator\Helper\TicketReserverHelper;

/**
 * Model which handles actions in the Admin Bookings form and Admin Edit Booking
 *
 */
class BookingModel extends AdminModel
{

	/**
	 * Method to get the record form.
	 *
	 */
	public function getForm($data = array(), $loadData = true)
	{
		$form = $this->loadForm(
			'com_u3abooking.booking',
			'booking',
			array(
				'control' => 'jform',
				'load_data' => $loadData
			)
		);
		if (empty($form))
		{
			return false;
		}
		return $form;
	}
 	
	/**
	 * Method to get the data that should be injected in the form.
	 *
	 */
	protected function loadFormData()
	{
		// get data which the user previously entered into the form
		// the context 'com_u3abooking.edit.booking.data' is set in FormController
		$data = Factory::getApplication()->getUserState(
			'com_u3abooking.edit.booking.data',
			array()
		);
		if (empty($data))
		{
			$data = $this->getItem();
		}

		return $data;
	}
	
	public function validate($form, $data, $group = null)
	{
		return parent::validate($form, $data, $group);
	}

	protected function prepareTable($table)
	{
	}
	
	/**
	 * Method to delete the selected bookings
	 * For each we need to unreserve the places booked at the event
	 * This involves calling the unreserveTickets method in the helper
	 * using the event_id and num_tickets found in each booking record.
	 */
	public function delete(&$pks)
	{
        $bookingTable = $this->getTable('booking');
		foreach ($pks as $pk)
		{
			$bookingTable->load($pk);
			
			if ($bookingTable->id)
			{
				TicketReserverHelper::unreserveTickets($bookingTable->event_id, $bookingTable->num_tickets);
			}
			else
			{
				Log::add('Admin delete: booking table load failed', Log::ERROR, 'u3a-error');
			}
			$deletePks = array($pk);
			parent::delete($deletePks);
		}
		return true;
	}
	
	/**
	 * Method to generate a CSV export of the bookings associated with the event
	 * which is passed by its event id in the parameter.
	 * This involves calling the unreserveTickets method in the event model
	 * using the event_id and num_tickets found in each booking record.
	 */
	public function csvexport($eventid)
	{
		$f = fopen('php://output', 'w'); 
		
		// write the header row
		$row = array('Id', 'Booking ref', 'Telephone', 'Email address', 'Tickets', 'Attendees', 'Special requirements', 'Booking date');
		fputcsv($f, $row);
		
		$db = $this->getDatabase();
		$query = $db->getQuery(true);

		$query->select('id, CONCAT(id,booking_ref_part) AS booking_ref, telephone, email, num_tickets, attendees, special_requirements, created')
				->from($db->quoteName('#__u3a_booking'))
				->where($db->quoteName('event_id') . ' = ' . $eventid)
				->order($db->quoteName('id'));
		$db->setQuery($query);
		
		$rows = $db->loadRowList();
		
		foreach ($rows as $row)
		{
			fputcsv($f, $row);
		}
		
		// get the alias of the event - to set as the filename of the csv file
		$eventTable = $this->getTable('event');
		$eventTable->load($eventid);
		$csvFilename = $eventTable->alias ? $eventTable->alias . ".csv" : "event.csv";
		
		// set up the http headers to indicate file download
		header( 'Content-Type: application/csv' );
		header( 'Content-Disposition: attachment; filename="' . $csvFilename . '";' );
		
		return true;
	}

}