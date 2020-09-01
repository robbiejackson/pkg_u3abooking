<?php

defined('_JEXEC') or die('Restricted access');

use Joomla\Registry\Registry;
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\Factory;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Log\Log;

/**
 * Model which handles actions in the Admin Events form and Admin Edit Event
 *
 */
class U3ABookingModelEvent extends AdminModel
{

	/**
	 * Method to get the U3A Booking Event table object
	 */
	 
	public function getTable($type = 'Event', $prefix = 'U3ABookingTable', $config = array())
	{
		return Table::getInstance($type, $prefix, $config);
	}

	/**
	 * Method to get the record form.
	 *
	 */
	public function getForm($data = array(), $loadData = true)
	{
		$form = $this->loadForm(
			'com_u3abooking.event',
			'event',
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
		// the context 'com_u3abooking.edit.event.data' is set in FormController
		$data = Factory::getApplication()->getUserState(
			'com_u3abooking.edit.event.data',
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
		if ($data['event_start'] >= $data['event_end'])
		{
			$this->setError("Event end date/time must be after Event start date/time");
			return false;
		}

		return parent::validate($form, $data, $group);
	}
	
	/* 
	 * Function to reserve tickets for an event
	 * 
	 * Parameters
	 *    the id of the event
	 *    the number of tickets to reserve
	 *    whether to force the reservation above the total capacity of the event
	 *
	 * It does this by adding the number requested to the number already booked,
	 *     if $forceReserve is false checking that this doesn't go over the limit.
	 * If the limit is reached, it tries to reserve as many as possible.
	 * If $forceReserve is true it just books that number of tickets.
	 * 
	 * It returns the number successfully booked
	 */
	
	public function reserveTickets($eventid, $num_tickets, $forceReserve = false)
	{
		$db = Factory::getDbo(); 
		try
		{
			$db->transactionStart();
			
			// set a row-level lock on the event record to avoid problems with concurrent access
			$query = $db->getQuery(true);
			$query->select($db->quoteName(array('capacity','tickets_taken')))
				->from($db->quoteName('#__u3a_event'))
				->where($db->quoteName('id') . '=' . $eventid);
			$db->setQuery($query . " FOR UPDATE");
			
			$row = $db->loadObject();
			if ($forceReserve)
			{
				$new_tickets_taken = (int)$row->tickets_taken + (int)$num_tickets;
				$places_allocated = (int)$num_tickets;
			}
			else
			{
				if ((int)$row->tickets_taken >= (int)$row->capacity) 
				{
					$db->transactionRollback();
					return 0;
				}
				
				if ((int)$row->tickets_taken + (int)$num_tickets <= (int)$row->capacity)   // all tickets requested are available
				{
					$new_tickets_taken = (int)$row->tickets_taken + (int)$num_tickets;
					$places_allocated = (int)$num_tickets;
				}
				else  // allocate just some of the tickets, up to the capacity
				{
					$places_allocated = (int)$row->capacity - (int)$row->tickets_taken;
					$new_tickets_taken = (int)$row->capacity;
				}
			}

			// update the tickets_taken database field with the new value
			$query = $db->getQuery(true);
			$fields = array(
				$db->quoteName('tickets_taken') . ' = ' . $new_tickets_taken
			);
			$conditions = array(
				$db->quoteName('id') . '='. $eventid
			);
			$query->update($db->quoteName('#__u3a_event'))->set($fields)->where($conditions);
			$db->setQuery($query);
			$result = $db->execute();

			$db->transactionCommit();  // unlocks the row
			
			return $places_allocated; 
		}
		catch (Exception $e)
		{
			// catch any database errors.
			$db->transactionRollback();
			JErrorPage::render($e);
		}
	}
	
	/* 
	 * Function to unreserve tickets for an event
	 * It returns true providing a database error hasn't been encountered
	 */
	
	public function unreserveTickets($eventid, $num_tickets)
	{
		$db = Factory::getDbo(); 
		try
		{
			$db->transactionStart();
			
			// set a row-level lock on the event record to avoid problems with concurrent access
			$query = $db->getQuery(true);
			$query->select($db->quoteName(array('capacity','tickets_taken')))
				->from($db->quoteName('#__u3a_event'))
				->where($db->quoteName('id') . '=' . $eventid);
			$db->setQuery($query . " FOR UPDATE");
			
			$row = $db->loadObject();
			
			$new_tickets_taken = $row->tickets_taken - $num_tickets;
			
			if ($new_tickets_taken < 0)
			{
				Log::add('Unreserve tickets gets tickets taken < 0', Log::ERROR, 'u3a-error');
				$new_tickets_taken = 0;
			}
			
			// update the tickets_taken database field with the new value
			$query = $db->getQuery(true);
			$fields = array(
				$db->quoteName('tickets_taken') . ' = ' . $new_tickets_taken
			);
			$conditions = array(
				$db->quoteName('id') . '='. $eventid
			);
			$query->update($db->quoteName('#__u3a_event'))->set($fields)->where($conditions);
			$db->setQuery($query);
			$result = $db->execute();

			$db->transactionCommit();  // unlocks the row
			
			return true; 
		}
		catch (Exception $e)
		{
			// catch any database errors.
			$db->transactionRollback();
			JErrorPage::render($e);
		}
	}
	
	/**
	 * Prepare a record for saving in the database 
	 * We just use this to set a new record with 'ordering' set to the end
	 */
	protected function prepareTable($table)
	{
		// Set ordering to the last item if not set
		if (empty($table->ordering))
		{
			$db = $this->getDbo();
			$query = $db->getQuery(true)
				->select('MAX(ordering)')
				->from('#__u3a_event');

			$db->setQuery($query);
			$max = $db->loadResult();

			$table->ordering = $max + 1;
		}
	}

	protected function cleanCache($group = null, $client_id = 0)
	{
		parent::cleanCache('com_u3abooking');
	}
}