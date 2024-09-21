<?php
namespace Robbie\Component\U3ABooking\Administrator\Table;

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Table\Table;
use Joomla\CMS\Factory; 
use Joomla\CMS\Filter\OutputFilter;
use Joomla\CMS\Language\Text;


class EventTable extends Table
{
	function __construct(&$db)
	{
        parent::__construct('#__u3a_event', 'id', $db);
	}

	public function check()
	{
		// Remove white space in alias field, and generate it from the title if it's not set
		$this->alias = trim($this->alias);
		if (empty($this->alias))
		{
			$this->alias = $this->title;
		}
		$this->alias = OutputFilter::stringURLSafe($this->alias);
		return true;
	}

	public function delete($pk = null)
	// To do - delete associated bookings
	{
		return parent::delete($pk);
	}
	
	public function store($updateNulls = false)
	{
		$date = Factory::getDate();
		$user = Factory::getApplication()->getIdentity();

		if (!$this->id)
		{
			// New event. Set the created date and created_by user fields
			if (!(int) $this->created)
			{
				$this->created = $date->toSql();
			}
			if (empty($this->created_by))
			{
				$this->created_by = $user->get('id');
			}
		}

		// Verify that the alias is unique
		$table = new EventTable($this->_db);

		if ($table->load(array('alias' => $this->alias)) && ($table->id != $this->id || $this->id == 0))
		{
			$this->setError(Text::_('COM_U3ABOOKING_ERROR_NONUNIQUE_ALIAS'));
			return false;
		}

		return parent::store($updateNulls);
	}
}