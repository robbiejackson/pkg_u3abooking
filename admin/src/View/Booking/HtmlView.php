<?php
namespace Robbie\Component\U3ABooking\Administrator\View\Booking;

/**
 * View which provides the form for editing a booking
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\Factory;

class U3ABookingViewBooking extends HtmlView
{

	protected $form = null;
    protected $canDo;

	/**
	 * Display of the Edit Booking form
	 */
	public function display($tpl = null)
	{
		// Get the Data
		$this->form = $this->get('Form');
		$this->item = $this->get('Item');

		$this->canDo = JHelperContent::getActions('com_u3abooking', 'booking', $this->item->id);
        
		if (count($errors = $this->get('Errors')))
		{
			JError::raiseError(500, implode('<br />', $errors));
			return false;
		}

		$this->addToolBar();

		parent::display($tpl);

		$this->setDocument();
	}

	/**
	 * Add the page title and toolbar.
	 *
	 */
	protected function addToolBar()
	{
		$input = Factory::getApplication()->input;

		// Hide Joomla Administrator Main menu
		$input->set('hidemainmenu', true);

		$isNew = ($this->item->id == 0);
		
		// Set the page header and glyph (https://docs.joomla.org/J3.x:Joomla_Standard_Icomoon_Fonts)
		JToolBarHelper::title(JText::_('COM_U3ABOOKING_BOOKING_HEADING_EDIT'), 'pencil-2');
									 
		// Build the actions for new and existing records. Omit Save as Copy
		if ($this->canDo->get('core.edit'))
		{
			// We can save the new record
			JToolBarHelper::apply('booking.apply', 'JTOOLBAR_APPLY');   // Save
			JToolBarHelper::save('booking.save', 'JTOOLBAR_SAVE');	  // Save and Close
		}
		JToolBarHelper::cancel('booking.cancel', 'JTOOLBAR_CLOSE');
	}
	
	/**
	 * Method to set up the document properties
	 */
	protected function setDocument() 
	{
		$isNew = ($this->item->id < 1);
		$document = Factory::getDocument();
		$document->setTitle(JText::_('COM_U3ABOOKING_BOOKING_EDITING'));
		// add script which performs the js validation for the booking reference part
		$document->addScript(JURI::root() . 'administrator/components/com_u3abooking/models/forms/bookingref.js');
		JText::script('COM_U3ABOOKING_BOOKING_ERROR_UNACCEPTABLE');
	}
}