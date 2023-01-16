<?php
namespace Robbie\Component\U3ABooking\Site\Model;
/**
 * Model for get an individual event for booking
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\Form\Form;
use Joomla\CMS\Factory;
use Joomla\CMS\Date\Date;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Language\Text;

class BookingModel extends AdminModel
{

	private $bookingTable;

	protected function populateState()
	{
		parent::populateState();
	}
	
	public function getBookingRecordId()
	{
		$bookingRecordId = (isset($this->bookingTable)) ? $this->bookingTable->getRecordId() : -1;
		$this->setState('booking.id', $bookingRecordId);
		return $bookingRecordId;
	}

	/**
	 * Get the booking record and return it
	 */
	public function getItem($id = null)
	{
		if (!isset($this->item) || !is_null($id)) 
		{
			$id = is_null($id) ? $this->getState('booking.id') : $id;
			
			if (!$id)
			{
				return null;
			}
			
			$db = $this->getDatabase();

			$query = $db->getQuery(true);
			$query->select('b.id as id, b.event_id as event_id, b.booking_ref_part as booking_ref_part, concat(b.id, b.booking_ref_part) as booking_ref,
					b.num_tickets as num_tickets, b.attendees as attendees, b.telephone as telephone, b.email as email, 
					b.special_requirements as special_requirements, b.created as created, b.created_by as created_by')
				  ->from('#__u3a_booking as b')
				  ->where('b.id=' . (int)$id);

			$db->setQuery($query);
		
			$this->item = $db->loadObject();
		}
		return $this->item;
	}
	
	// get the form allowing the user to book places at an event
	public function getForm($data = array(), $loadData = true)
	{
		$form = $this->loadForm(
			'com_u3abooking.form',
			'booking',
			array(
				'control' => 'jform',
				'load_data' => $loadData
			)
		);

		if (empty($form))
		{
            $errors = $this->getErrors();
			throw new \Exception(implode("\n", $errors), 500);
		}

		return $form;
	}
	
	// get the data for pre-filling the fields of the booking form
	protected function loadFormData()
	{
		// Check the session for previously entered form data.
		$app = Factory::getApplication();
		$layout = $app->input->get('layout', '');
		$data = Factory::getApplication()->getUserState(
			"com_u3abooking.$layout.booking.data",
			array()
		);
		
		if (empty($data))
		{
			// try to prefill from database booking record
			$data = $this->getItem();
			if (empty($data))
			{
				// no existing record (ie not amending a booking) - set the event_id
				$data["event_id"] = $this->getState('event.id');
			}
		}
		else
		{
			// we've got session data ok - ensure we've got the right event id in the session data
			// (just in case something went wrong with a previous booking on another event and the session data relates to that)
			$data["event_id"] = $this->getState('event.id') ? $this->getState('event.id') : $data["event_id"];
		}

		return $data;
	}
	
	/**
	 * Method to preprocess the form to make any changes dynamically
	 * This just sets the max number of tickets that an individual can book on this event
	 * The state variable is set in the view.html.php file
	 */
	protected function preprocessForm(Form $form, $data, $group = 'event')
	{
		$form->setFieldAttribute("num_tickets", "max", $this->getState('event.max_tickets_per_booking'));
	}
	
	/**
	 * Method to validate the form data
	 * After calling the standard validation it checks that the num_tickets booked
	 * doesn't exceed the max_tickets_per_booking value set in the event record
	 * (Can't use the state variable above as the view isn't called in the POST handling)
	 */
	public function validate($form, $data, $group = null)
	{
		$data = parent::validate($form, $data, $group);
		if ($data)  // no validation errors from Joomla validation
		{
			$eventTable = getTable('Event');
			$eventTable->load($data["event_id"]);
			if ($data['num_tickets'] > $eventTable->max_tickets_per_booking)
			{
				$this->setError(Text::_('COM_U3ABOOKING_TOO_MANY_TICKETS_BOOKED'), 'error');
				return false;
			}
			return $data;
		}
		return $data;
	}
	
	/**
	 * Method to delete a booking
	 * It assumes that the user may delete this booking, so doesn't check ACL
	 * The id is passed in an array to match AdminModel, but there's only ever 1 entry.
	 */
	public function delete(&$pks)
	{
		foreach ($pks as $i => $id)
		{
			$db = $this->getDatabase();

			$query = $db->getQuery(true);

			$query->delete($db->quoteName('#__u3a_booking'))
				->where($db->quoteName('id') . " = $id");

			$db->setQuery($query);

			$result = $db->execute();
		}
	}
}