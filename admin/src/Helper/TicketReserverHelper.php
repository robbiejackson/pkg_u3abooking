<?php
/**
 * Helper file for reserving and unreserving tickets
 */

namespace Robbie\Component\U3ABooking\Administrator\Helper;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\Database\DatabaseInterface;
use Joomla\CMS\Log\Log;

class TicketReserverHelper
{
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
	
	public static function reserveTickets($eventid, $num_tickets, $forceReserve = false, $db = false)
	{
        if (!$db) {
            $db = Factory::getContainer()->get(DatabaseInterface::class);
        }
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
		catch (\Exception $e)
		{
			// catch any database errors.
			$db->transactionRollback();
            throw new \Exception(implode("\n", $e), 500);
		}
	}
	
	/* 
	 * Function to unreserve tickets for an event
	 * It returns true providing a database error hasn't been encountered
	 */
	
	public static function unreserveTickets($eventid, $num_tickets, $db = false)
	{
        if (!$db) {
            $db = Factory::getContainer()->get(DatabaseInterface::class);
        }
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
		catch (\Exception $e)
		{
			// catch any database errors.
			$db->transactionRollback();
            throw new \Exception(implode("\n", $e), 500);
		}
	}
}
