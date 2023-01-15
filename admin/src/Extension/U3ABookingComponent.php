<?php

namespace Robbie\Component\Helloworld\Administrator\Extension;

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Categories\CategoryServiceInterface;
use Joomla\CMS\Categories\CategoryServiceTrait;
use Joomla\CMS\Extension\MVCComponent;
use Joomla\Database\DatabaseAwareTrait;
    
class U3ABookingComponent extends MVCComponent implements CategoryServiceInterface
{
	use CategoryServiceTrait;
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
