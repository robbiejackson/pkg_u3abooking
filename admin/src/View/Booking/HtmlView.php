<?php
namespace Robbie\Component\U3ABooking\Administrator\View\Booking;

/**
 * View which provides the form for editing a booking
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Factory;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

class HtmlView extends BaseHtmlView 
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

		$this->canDo = ContentHelper::getActions('com_u3abooking', 'booking', $this->item->id);
        
		if (count($errors = $this->get('Errors')))
		{
			throw new \Exception(implode("\n", $errors), 500); 
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
		ToolBarHelper::title(Text::_('COM_U3ABOOKING_BOOKING_HEADING_EDIT'), 'pencil-2');
									 
		// Build the actions for new and existing records. Omit Save as Copy
		if ($this->canDo->get('core.edit'))
		{
			// We can save the new record
			ToolBarHelper::apply('booking.apply', 'JTOOLBAR_APPLY');   // Save
			ToolBarHelper::save('booking.save', 'JTOOLBAR_SAVE');	  // Save and Close
		}
		ToolBarHelper::cancel('booking.cancel', 'JTOOLBAR_CLOSE');
	}
	
	/**
	 * Method to set up the document properties
	 */
	protected function setDocument() 
	{
		$isNew = ($this->item->id < 1);
		$this->document->setTitle(Text::_('COM_U3ABOOKING_BOOKING_EDITING'));
	}
}