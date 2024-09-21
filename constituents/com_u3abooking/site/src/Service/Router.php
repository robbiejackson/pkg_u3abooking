<?php

namespace Robbie\Component\U3ABooking\Site\Service;

defined('_JEXEC') or die;

use Joomla\CMS\Component\Router\RouterInterface;
use Joomla\CMS\Factory;
use Joomla\CMS\Categories\CategoryFactoryInterface;
use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\Database\DatabaseInterface;

class Router implements RouterInterface
{
    private $application;
    
    private $menu;
    
    private $categoryFactory;

    private $db;
    
    private $categories;
    
    public function __construct($application, $menu, CategoryFactoryInterface $categoryFactory, DatabaseInterface $db)
    {
        $this->application = $application;
        $this->menu = $menu;
        $this->categoryFactory = $categoryFactory;
        $this->db = $db;
    }

	public function build(&$query)
	{
		$segments = array();
        
        // We need an Itemid and a view
		if (!isset($query['view']) || !isset($query['Itemid']))
		{
			return $segments;
		}

		$app  = Factory::getApplication();
        
		// get the menu item that this call to build() relates to
		$sitemenu = $app->getMenu();
		$thisMenuitem = $sitemenu->getItem($query['Itemid']);

        if (($query['view'] == "events"))
        {
            // nothing to do
        }
        elseif ($query['view'] == "booking" && isset($query['layout']) && $query['layout'] == "add")
        {
            // New booking. Use the scheme /eventid:alias/book
            // There should be an eventid parameter, find the associated alias
            if (!isset($query['eventid'])) {
                return $segments;
            }
            $segments[] = $query['eventid'];
            unset($query['eventid']);
            $segments[] = 'book';
            unset($query['layout']);
        }
        elseif ($query['view'] == "booking" && isset($query['layout']) && $query['layout'] == "edit")
        {
            // Edit existing booking. Use the scheme /eventid:alias/edit_booking + query params
            if (!isset($query['eventid'])) {
                return $segments;
            }
            $segments[] = $query['eventid'];
            unset($query['eventid']);
            $segments[] = 'edit_booking';
            unset($query['layout']);
        }

		unset($query['view']);
		return $segments;
	}
    
	public function parse(&$segments)
	{
		$vars = array();
		$nSegments = count($segments);
        
		$app  = Factory::getApplication();
		$sitemenu = $app->getMenu();
		$activeMenuitem = $sitemenu->getActive();
		if (!$activeMenuitem)
		{
			return $vars;
		}
        
		if ($activeMenuitem->query['view'] == "events")
		{
            $vars['view'] = 'events';
		}
		elseif ($activeMenuitem->query['view'] == "booking")
		{
			$vars['view'] = 'booking';
            // For scheme /event/eventid/book we get segments eventid and book
            // For scheme /event/eventid/edit_booking we get segments eventid, edit_booking and params in query
            if ($nSegments == 2 && ($segments[1] == 'book' || $segments[1] == 'edit_booking'))
            {
                $vars['layout'] = ($segments[1] == 'book') ? 'add' : 'edit';
                $vars['eventid'] = (int)$segments[0];
                unset($segments[0]);
                unset($segments[1]);
            }
		}

		return $vars;
	}
  
	public function preprocess($query)
	{
        $app  = Factory::getApplication();
        $sitemenu = $app->getMenu("site");

        if (isset($query['Itemid']))
        {
            return $query;
            // If the query parameters match that menuitem exactly then unset the id
            // Note that the id can be in the form <record id>:<record alias> so we use (int) to get just the <record id>
            // Find the menuitem whose id is the Itemid passed in
            //$thisMenuitem = $sitemenu->getItem($query['Itemid']);
            // check the query params of that menuitem to see if it matches with the helloworld or category id passed in
            /*
            if (array_key_exists('id', $query) && array_key_exists('id', $thisMenuitem->query) && $thisMenuitem->query['id'] == (int)$query['id'])
            {
                unset($query['id']);
                if (array_key_exists('catid', $query)) {
                    unset($query['catid']);
                }
            }
            */
        } 
        else
        {
            // No Itemid set, so try to find a  menuitem which matches the query params
            $menuitems = $sitemenu->getItems(array('component'), array('com_u3abooking'));
            foreach ($menuitems as $menuitem)
            {
                if (array_key_exists('view', $query) && array_key_exists('view', $menuitem->query) &&
                    ($menuitem->query['view'] == $query['view']))
                {
                    $query['Itemid'] = $menuitem->id;
                    // if there's an exact match with the eventid as well, then take that menuitem by preference, and remove the eventid from the query
                    if (array_key_exists('eventid', $query) && array_key_exists('eventid', $menuitem->query) && ($menuitem->query['eventid'] == (int)$query['eventid']))
                    {
                        break;
                    }
                }
            }
        }
        return $query;
	}
}