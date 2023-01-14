<?php

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\CMS\Factory;

/**
 * List Model for displaying the Events on the Admin Events form
 *
 */
class U3ABookingModelEvents extends ListModel
{
	public function __construct($config = array())
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(   // fields whose column headers you can click on in the Events form, to sort by them
				'id',
				'ordering',
				'title',
				'venue',
				'event_start',
                'organiser',
                'created',
				'category_id',
				'access',
				'published'
                );
		}

		parent::__construct($config);
	}
    
    protected function populateState($ordering = 'ordering', $direction = 'asc')
	{
		$app = Factory::getApplication();

		// Adjust the context to support modal layouts.
		if ($layout = $app->input->get('layout'))
		{
			$this->context .= '.' . $layout;
		}

		parent::populateState($ordering, $direction);
	}

	/**
	 * Method to build an SQL query to load the list data.
	 *
	 * @return      string  An SQL query
	 */
	protected function getListQuery()
	{
		// Initialize variables.
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);
		$user = Factory::getUser();
		
		// Create the base select statement.
		$query->select('e.id as id, e.title as title, e.venue as venue, e.event_start as event_start, e.event_end as event_end,
				  e.capacity as capacity, e.tickets_taken as tickets_taken, e.organiser_email as organiser,
				  e.published as published, e.created as created, e.access as access,
				  e.checked_out as checked_out, e.checked_out_time as checked_out_time, e.catid as catid,
				  e.ordering as ordering, e.image as imageInfo, e.alias as alias')
			  ->from($db->quoteName('#__u3a_event', 'e'));

        // Join over the categories.
		$query->select($db->quoteName('c.title', 'category_title'))
			->join('LEFT', $db->quoteName('#__categories', 'c') . ' ON c.id = e.catid');
        
        // Join with users table to get the username of the author
		$query->select($db->quoteName('u.username', 'author'))
			->join('LEFT', $db->quoteName('#__users', 'u') . ' ON u.id = e.created_by');
            
		// Join with users table to get the username of the person who checked the record out
		$query->select($db->quoteName('u2.username', 'editor'))
			->join('LEFT', $db->quoteName('#__users', 'u2') . ' ON u2.id = e.checked_out');
		      
		// Join over the access levels, to get the name of the access level
		$query->select('v.title AS access_level')
			->join('LEFT', '#__viewlevels AS v ON v.id = e.access');
		
        // Filter: like / search
		$search = $this->getState('filter.search');

		if (!empty($search))
		{
			$like = $db->quote('%' . $search . '%');
			$query->where('title LIKE ' . $like);
		}
		
		// Filter by event start date - by default filter on dates from today on
		$event_start = $this->getState('filter.event_start', date('Y-m-d'));
		if (is_numeric(strtotime($event_start)))
		{
			$query->where("e.event_start >= '$event_start'");
		}

		// Filter by published state
		$state = $this->getState('filter.published');
		if (is_numeric($state))
		{
			$query->where('e.published = ' . (int) $state);
		}
		elseif ($state === '')
		{
			$query->where('(e.published IN (0, 1))');
		}

		// Filter by categories
		$catid = $this->getState('filter.category_id');
		if ($catid)
		{
			$query->where("e.catid = " . $db->quote($db->escape($catid)));
		}
		
		// Display only records to which the user has access
		if (!$user->authorise('core.admin'))  // ie if not SuperUser
		{
			$userAccessLevels = implode(',', $user->getAuthorisedViewLevels());
			$query->where('e.access IN (' . $userAccessLevels . ')');
			$query->where('c.access IN (' . $userAccessLevels . ')');
		}
		
		// Add the list ordering clause.
		$orderCol   = $this->state->get('list.ordering', 'event_start');
		$orderDirn 	= $this->state->get('list.direction', 'desc');

		$query->order($db->escape($orderCol) . ' ' . $db->escape($orderDirn));

		return $query;
	}
}