<?php
namespace Robbie\Component\U3ABooking\Administrator\View\Bookings;

/**
 * This is the view file which collates the items to display on the Admin Bookings form
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Toolbar\ToolbarHelper;

class HtmlView extends BaseHtmlView 
{
	/**
	 * Display the Bookings view
	 */
	function display($tpl = null)
	{
		// Get application
		$app = Factory::getApplication();

        // Get data from the model
		$this->items			= $this->get('Items');
		$this->pagination		= $this->get('Pagination');
		$this->state			= $this->get('State');
		$this->filterForm    	= $this->get('FilterForm');
		$this->activeFilters 	= $this->get('ActiveFilters');
        
		$this->canDo = ContentHelper::getActions('com_u3abooking');

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			throw new \Exception(implode("\n", $errors), 500);
		}
        
		$this->addToolBar();

		parent::display($tpl);

		$this->setupDocument();
	}

	/**
	 * Add the page title and toolbar.
	 */
	protected function addToolBar()
	{
		$title = Text::_('COM_U3ABOOKING_BOOKINGS_TITLE');
		
		ToolbarHelper::title($title);
		if ($this->canDo->get('core.edit')) 
		{
			ToolbarHelper::editList('booking.edit', 'JTOOLBAR_EDIT');
		}
		if ($this->canDo->get('core.delete'))
		{
			ToolbarHelper::deleteList('COM_U3ABOOKING_BOOKINGS_CONFIRM_DELETE', 'bookings.delete', 'JTOOLBAR_DELETE');
		}
		
		// add the download button (as a custom button)
		ToolbarHelper::custom('bookings.csvexport', 'download', '', 'COM_U3ABOOKING_BOOKINGS_DOWNLOAD', false);

	}
	/**
	 * Method to set up the document properties
	 *
	 * @return void
	 */
	protected function setupDocument() 
	{
		$this->document->setTitle(Text::_('COM_U3ABOOKING_ADMINISTRATION_BOOKINGS'));
	}
}