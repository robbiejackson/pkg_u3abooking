<?php

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Table\Table;

class U3ABookingTableBooking extends Table
{
	function __construct(&$db)
	{
		parent::__construct('#__u3a_booking', 'id', $db);
	}
	
	function getRecordId()
	{
		return $this->id;
	}
}