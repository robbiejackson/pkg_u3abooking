<?php
/**
 * This is the view file which collates the items to display on the Admin Bookings form
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\Factory;

class U3ABookingViewBookings extends HtmlView
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
        
		$this->canDo = JHelperContent::getActions('com_u3abooking');

		// Check for errors.
		if (count($errors = $this->get('Errors')))
		{
			JError::raiseError(500, implode('<br />', $errors));

			return false;
		}
        
		U3ABookingHelper::addSubmenu('bookings');
		$this->addToolBar();

		parent::display($tpl);

		$this->setDocument();
	}

	/**
	 * Add the page title and toolbar.
	 */
	protected function addToolBar()
	{
		$title = JText::_('COM_U3ABOOKING_BOOKINGS_TITLE');
		
		$bar = JToolbar::getInstance('toolbar');
	
		JToolBarHelper::title($title);
		if ($this->canDo->get('core.edit')) 
		{
			JToolBarHelper::editList('booking.edit', 'JTOOLBAR_EDIT');
		}
		if ($this->canDo->get('core.delete'))
		{
			JToolbarHelper::deleteList('COM_U3ABOOKING_BOOKINGS_CONFIRM_DELETE', 'bookings.delete', 'JTOOLBAR_DELETE');
		}
		
		// add the download button (as a custom button)
		JToolbarHelper::custom('bookings.csvexport', 'download', '', 'COM_U3ABOOKING_BOOKINGS_DOWNLOAD', false);

		// Add a batch button
		/*
		if ($this->canDo->get('core.create') && $this->canDo->get('core.edit')
				&& $this->canDo->get('core.edit.state'))
		{
				// we use a standard Joomla layout to get the html for the batch button
				$layout = new JLayoutFile('joomla.toolbar.batch');
				$batchButtonHtml = $layout->render(array('title' => JText::_('JTOOLBAR_BATCH')));
				$bar->appendButton('Custom', $batchButtonHtml, 'batch');
		}
		if ($this->canDo->get('core.admin')) 
		{
			JToolBarHelper::divider();
			JToolBarHelper::preferences('com_u3abooking');
		}
		*/
	}
	/**
	 * Method to set up the document properties
	 *
	 * @return void
	 */
	protected function setDocument() 
	{
		$document = Factory::getDocument();
		$document->setTitle(JText::_('COM_U3ABOOKING_ADMINISTRATION_BOOKINGS'));
	}
}