<?php
namespace Robbie\Component\U3ABooking\Site\Model;
/**
 * Model for get an individual event for booking
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\MVC\Model\ItemModel;
use Joomla\CMS\Factory;
use Joomla\CMS\Date\Date;
use Joomla\CMS\Log\Log;

class EventModel extends ItemModel
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
}