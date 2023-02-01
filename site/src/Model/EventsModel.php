<?php
namespace Robbie\Component\U3ABooking\Site\Model;
/**
 * Model for get all the available events
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\CMS\Factory;
use Joomla\CMS\Date\Date;

class EventsModel extends ListModel
{
	/**
	 * Method to build an SQL query to load the list of events
	 *
	 * @return      string  An SQL query
	 */
	protected function getListQuery()
	{
		// Initialize variables.
		$db    = $this->getDatabase();
		$query = $db->getQuery(true);
		$user = Factory::getApplication()->getIdentity();
		
		$date = Factory::getDate();
		$today = $db->quote($date->toSql());

		// Create the base select statement.
		$query->select('e.id as id, e.title as title, e.alias as alias, e.venue as venue, 
                  e.event_start as event_start, e.event_end as event_end, e.capacity as capacity, 
                  e.tickets_taken as tickets_taken, e.access as access, e.catid as catid')
			  ->from($db->quoteName('#__u3a_event', 'e'));

        // Join over the categories.
		//$query->select($db->quoteName('c.title', 'category_title'))
		$query->join('LEFT', $db->quoteName('#__categories', 'c') . ' ON c.id = e.catid');

		// display published events only
		$query->where('e.published = 1');
		
		// display only events with dates in the future
		$query->where('e.event_start >= ' . $today);

		// Display only records to which the user has access
		if (!$user->authorise('core.admin'))  // ie if not SuperUser
		{
			$userAccessLevels = implode(',', $user->getAuthorisedViewLevels());
			$query->where('e.access IN (' . $userAccessLevels . ')');
			$query->where('c.access IN (' . $userAccessLevels . ')');
		}

		$query->order('event_start ASC');

		return $query;
	}
}