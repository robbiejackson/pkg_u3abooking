<?php

namespace Robbie\Component\U3ABooking\Administrator\Extension;

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Categories\CategoryServiceInterface;
use Joomla\CMS\Categories\CategoryServiceTrait;
use Joomla\CMS\Component\Router\RouterServiceTrait;
use Joomla\CMS\Component\Router\RouterServiceInterface;
use Joomla\CMS\Extension\MVCComponent;
use Joomla\Database\DatabaseAwareTrait;
    
class U3ABookingComponent extends MVCComponent implements CategoryServiceInterface, RouterServiceInterface
{
	use CategoryServiceTrait;
    use RouterServiceTrait;
    use DatabaseAwareTrait;

	/**
	 * Returns the table name for the count items function for the given section of the category table
	 *
	 */
	protected function getTableNameForSection(string $section = null)
	{
		return 'u3a_event';
	}
    
    /**
	 * Returns the name of the published state column in the table
     * for use by the count items function
	 *
	 */
    protected function getStateColumnForSection(string $section = null)
    {
        return 'published';
    }

}
