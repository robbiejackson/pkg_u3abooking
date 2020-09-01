<?php

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\MVC\Model\ListModel;
use Joomla\CMS\Factory;

/**
 * List Model for displaying the Bookings on the Admin Bookings form
 *
 */
class U3ABookingModelBookings extends ListModel
{
	public function __construct($config = array())
	{
		if (empty($config['filter_fields']))
		{
			$config['filter_fields'] = array(   // fields whose column headers you can click on in the Bookings form, to order by them
				'event_id',
				'event_title',
				'event_start',
				'id',
				'booking_reference',
				'num_tickets',
				'telephone',
				'email',
                'username',
                'created',
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
	 * @return      string  A SQL query
	 */
	protected function getListQuery()
	{
		// Initialize variables.
		$db    = Factory::getDbo();
		$query = $db->getQuery(true);
		$user = Factory::getUser();
		
		// Create the base select statement.
		$query->select('e.id as event_id, e.title as event_title, e.event_start as event_start, e.event_end as event_end, 
				  b.id as id, CONCAT(b.id, b.booking_ref_part) as booking_reference, b.num_tickets as num_tickets, 
				  b.telephone as telephone, b.email as email, u.username as username, b.created as created')
			  ->from($db->quoteName('#__u3a_booking', 'b'))
			  ->join('LEFT', $db->quoteName('#__u3a_event', 'e') . ' ON e.id = b.event_id')
			  ->join('LEFT', $db->quoteName('#__users', 'u') . ' ON u.id = b.created_by');

        // Filter: like / search
		$search = $this->getState('filter.search');

		if (!empty($search))
		{
			$like = $db->quote('%' . $search . '%');
			$query->where('e.title LIKE ' . $like);
		}
	
		// Display only records to which the user has access
		if (!$user->authorise('core.admin'))  // ie if not SuperUser
		{
			$userAccessLevels = implode(',', $user->getAuthorisedViewLevels());
			$query->where('e.access IN (' . $userAccessLevels . ')');
		}
		
		// Filter by event, if the user has set that in the filter field
        $event = $this->getState('filter.event_id');
        if ($event)
        {
            $query->where('b.event_id = ' . $db->quote($event));
		}
		
		// Add the list ordering clause.
		$orderCol   = $this->state->get('list.ordering', 'ordering');
		$orderDirn 	= $this->state->get('list.direction', 'asc');

		$query->order($db->escape($orderCol) . ' ' . $db->escape($orderDirn));

		return $query;
	}
}