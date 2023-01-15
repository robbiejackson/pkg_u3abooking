<?php
namespace Robbie\Component\U3ABooking\Administrator\Table;

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Table\Table;

class BookingTable extends Table
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