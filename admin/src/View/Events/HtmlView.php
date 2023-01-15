<?php
namespace Robbie\Component\U3ABooking\Administrator\View\Events;

/**
 * This is the view file which collates the items to display on the Admin Events form
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
	 * Display the Events view
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

			return false;
		}
        
        // Set the toolbar, but not on the modal window
		if ($this->getLayout() !== 'modal')
		{
			$this->addToolBar();
		}

		parent::display($tpl);

		$this->setDocument();
	}

	/**
	 * Add the page title and toolbar.
	 *
	 * @return  void
	 *
	 * @since   1.6
	 */
	protected function addToolBar()
	{
		ToolbarHelper::title(Text::_('COM_U3ABOOKING_EVENTS_TITLE'));
		if ($this->canDo->get('core.create')) 
		{
			ToolbarHelper::addNew('event.add', 'JTOOLBAR_NEW');
		}
		if ($this->canDo->get('core.edit')) 
		{
			ToolbarHelper::editList('event.edit', 'JTOOLBAR_EDIT');
		}
		if ($this->canDo->get('core.edit.state'))
		{
			ToolbarHelper::publish('events.publish', 'JTOOLBAR_PUBLISH', true);
			ToolbarHelper::unpublish('events.unpublish', 'JTOOLBAR_UNPUBLISH', true);
			ToolbarHelper::checkin('events.checkin');
		}
		if ($this->state->get('filter.published') == -2 && $this->canDo->get('core.delete'))
		{
			ToolbarHelper::deleteList('COM_U3ABOOKING_EVENTS_CONFIRM_DELETE', 'events.delete', 'JTOOLBAR_EMPTY_TRASH');
		}
		elseif ($this->canDo->get('core.edit.state'))
		{
			ToolbarHelper::trash('events.trash');
		}

	}
	/**
	 * Method to set up the document properties
	 *
	 * @return void
	 */
	protected function setDocument() 
	{
		$this->document->setTitle(Text::_('COM_U3ABOOKING_ADMINISTRATION_EVENTS'));
	}
}