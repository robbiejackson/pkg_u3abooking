<?php
/**
 * Model for get an individual event for booking
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\MVC\Model\ItemModel;
use Joomla\CMS\Factory;
use Joomla\CMS\Date\Date;
use Joomla\CMS\Log\Log;

//JLoader::register('HelloworldHelperRoute', JPATH_ROOT . '/components/com_helloworld/helpers/route.php');

class U3ABookingModelEvent extends ItemModel
{
	protected $item;

	protected function populateState()
	{
		// Set the event id in the model state
		$input = Factory::getApplication()->input;
		$id = $input->get('eventid', 0, 'INT');
		
		// note that we can't just use event.id as the state variable. 
		// This is because the AdminModel sets event.id (because this is the "event" model) to be
		// the id=xxx parameter set in the URL
		// The code was later changed to use ItemModel instead of AdminModel, but this use of u3aevent.id remains
		$this->setState('u3aevent.id', $id);
		
		parent::populateState();
	}

	// Use the U3A Event table
	public function getTable($type = 'Event', $prefix = 'U3ABookingTable', $config = array())
	{
		return JTable::getInstance($type, $prefix, $config);
	}

	/**
	 * Get the event record and return it
	 * checking that the user has permission to view it ok
	 */
	public function getItem($id = null)
	{
		if (!isset($this->item) || !is_null($id)) 
		{
			$id    = is_null($id) ? $this->getState('u3aevent.id') : $id;

			$db    = Factory::getDbo();
			
			$date = Factory::getDate();
			$today = $db->quote($date->toSql());
			
			$query = $db->getQuery(true);
			$query->select('e.id as id, e.title as title, e.description as description, e.venue as venue, 
					e.event_start as event_start, e.event_end as event_end, e.capacity as capacity, e.tickets_taken as tickets_taken,
					e.max_tickets_per_booking as max_tickets_per_booking, e.alias as alias, 
					e.access as access, e.catid as catid, c.title as category, c.access as catAccess')
				  ->from('#__u3a_event as e')
				  ->leftJoin('#__categories as c ON e.catid=c.id')
				  ->where('e.id=' . (int)$id)
				  ->where('e.published=1')
				  ->where('e.event_start >= ' . $today);

			$db->setQuery((string)$query);
		
			if ($this->item = $db->loadObject()) 
			{
				// Check if the user can access this record (and category) - set up the item canAccess property based on this
				$user = Factory::getUser();
				$userAccessLevels = $user->getAuthorisedViewLevels();
				if ($user->authorise('core.admin')) // ie superuser
				{
					$this->item->canAccess = true;
				}
				else
				{
					if ($this->item->catid == 0)
					{
						$this->item->canAccess = in_array($this->item->access, $userAccessLevels);
					}
					else
					{
						$this->item->canAccess = in_array($this->item->access, $userAccessLevels) && in_array($this->item->catAccess, $userAccessLevels);
					}
				}
			}
            else
            {
                return false; // no record found
            }
		}
		return $this->item;
	}
	
	/* 
	 * Function to reserve tickets for an event
	 * It does this by adding the number requested to the number already booked,
	 * checking that this doesn't go over the limit.
	 * If the limit is reached, it tries to reserve as many as possible.
	 * It returns the number successfully booked
	 */
	
	public function reserveTickets($eventid, $num_tickets)
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
}