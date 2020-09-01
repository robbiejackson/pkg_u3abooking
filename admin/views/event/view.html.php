<?php
/**
 * View which provides the form for creating / editing an event
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\Factory;

class U3ABookingViewEvent extends HtmlView
{

	protected $form = null;
    protected $canDo;

	/**
	 * Display of the Edit Event form
	 */
	public function display($tpl = null)
	{
		// Get the Data
		$this->form = $this->get('Form');
		$this->item = $this->get('Item');
        $this->script = $this->get('Script');

		$this->canDo = JHelperContent::getActions('com_u3abooking', 'event', $this->item->id);
        
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
		JToolBarHelper::title($isNew ? JText::_('COM_U3ABOOKING_EVENT_HEADING_EDIT')
		                             : JText::_('COM_U3ABOOKING_EVENT_HEADING_NEW'), 'pencil-2');
									 
		// Build the actions for new and existing records. Omit Save as Copy
		if ($isNew)
		{
			// For new records, check the create permission.
			if ($this->canDo->get('core.create')) 
			{
				JToolBarHelper::apply('event.apply', 'JTOOLBAR_APPLY');
				JToolBarHelper::save('event.save', 'JTOOLBAR_SAVE');
			}
			JToolBarHelper::cancel('event.cancel', 'JTOOLBAR_CANCEL');
		}
		else
		{
			if ($this->canDo->get('core.edit'))
			{
				// We can save the new record
				JToolBarHelper::apply('event.apply', 'JTOOLBAR_APPLY');
				JToolBarHelper::save('event.save', 'JTOOLBAR_SAVE');
 
				// We can save this record, but check the create permission to see
				// if we can return to make a new one.
				if ($this->canDo->get('core.create')) 
				{
					JToolBarHelper::custom('event.save2new', 'save-new.png', 'save-new_f2.png',
					                       'JTOOLBAR_SAVE_AND_NEW', false);
				}
			}
			JToolBarHelper::cancel('event.cancel', 'JTOOLBAR_CLOSE');
		}
	}
	/**
	 * Method to set up the document properties
	 */
	protected function setDocument() 
	{
		$isNew = ($this->item->id < 1);
		$document = Factory::getDocument();
		$document->setTitle($isNew ? JText::_('COM_U3ABOOKING_EVENT_CREATING') :
                JText::_('COM_U3ABOOKING_EVENT_EDITING'));
		JText::script('COM_U3ABOOKING_EVENT_ERROR_UNACCEPTABLE');
	}
}