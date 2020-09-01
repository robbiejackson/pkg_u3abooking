<?php
/**
 * Code for adding the submenu in the sidebar
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Factory;

abstract class U3ABookingHelper extends JHelperContent
{
	/**
	 * Configure the submenu in the sidebar
	 */

	public static function addSubmenu($submenu) 
	{
		JHtmlSidebar::addEntry(
			JText::_('COM_U3ABOOKING_SUBMENU_EVENTS'),
			'index.php?option=com_u3abooking&view=events',
			$submenu == 'events'
		);
		
		JHtmlSidebar::addEntry(
			JText::_('COM_U3ABOOKING_SUBMENU_BOOKINGS'),
			'index.php?option=com_u3abooking&view=bookings',
			$submenu == 'bookings'
		);

		JHtmlSidebar::addEntry(
			JText::_('COM_U3ABOOKING_SUBMENU_CATEGORIES'),
			'index.php?option=com_categories&view=categories&extension=com_u3abooking',
			$submenu == 'categories'
		);

		// Set some global property
		$document = Factory::getDocument();
		if ($submenu == 'categories') 
		{
			$document->setTitle(JText::_('COM_U3ABOOKING_ADMINISTRATION_CATEGORIES'));
		}
		/*
		if (JComponentHelper::isEnabled('com_fields'))
		{
			JHtmlSidebar::addEntry(
				JText::_('JGLOBAL_FIELDS'),
				'index.php?option=com_fields&context=com_helloworld.helloworld',
				$submenu == 'fields.fields'
			);

			JHtmlSidebar::addEntry(
				JText::_('JGLOBAL_FIELD_GROUPS'),
				'index.php?option=com_fields&view=groups&context=com_helloworld.helloworld',
				$submenu == 'fields.groups'
			);
		}
		*/
	}
    
    /**
	 * Get the actions
	 */
	 /*
	public static function getActions($component = '', $section = '', $messageId = 0)
	{	
		$result	= new JObject;

		if (empty($messageId)) {
			$assetName = 'com_helloworld';
		}
		else {
			$assetName = 'com_helloworld.message.'.(int) $messageId;
		}

		$actions = JAccess::getActions('com_helloworld', 'component');

		foreach ($actions as $action) {
            $value = JFactory::getUser()->authorise($action->name, $assetName);
			$result->set($action->name, $value);
		}

		return $result;
	} */
	
	/*
	public static function getContexts()
	{
		JFactory::getLanguage()->load('com_helloworld', JPATH_ADMINISTRATOR);

		$contexts = array(
			'com_helloworld.helloworld' => JText::_('COM_HELLOWORLD_ITEMS'),
			'com_helloworld.categories' => JText::_('JCATEGORY')
		);

		return $contexts;
	}
	
	public static function validateSection($section, $item)
	{
		if (JFactory::getApplication()->isClient('site') && $section == 'form')
		{
			return 'helloworld';
		}
		if ($section != 'helloworld' && $section != 'form')
		{
			return null;
		}

		return $section;
	}
	*/
}