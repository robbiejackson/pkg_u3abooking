<?php
namespace Robbie\Component\U3ABooking\Administrator\View\Event;

/**
 * View which provides the form for creating / editing an event
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\MVC\View\HtmlView as BaseHtmlView;
use Joomla\CMS\Factory;
use Joomla\CMS\Helper\ContentHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Toolbar\ToolbarHelper;

class HtmlView extends BaseHtmlView 
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

		$this->canDo = ContentHelper::getActions('com_u3abooking', 'event', $this->item->id);
        
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
	 *
	 */
	protected function addToolBar()
	{
		$input = Factory::getApplication()->input;

		// Hide Joomla Administrator Main menu
		$input->set('hidemainmenu', true);

		$isNew = ($this->item->id == 0);
		
		// Set the page header and glyph (https://docs.joomla.org/J3.x:Joomla_Standard_Icomoon_Fonts)
		ToolbarHelper::title($isNew ? Text::_('COM_U3ABOOKING_EVENT_HEADING_EDIT')
		                             : Text::_('COM_U3ABOOKING_EVENT_HEADING_NEW'), 'pencil-2');
									 
		// Build the actions for new and existing records. Omit Save as Copy
		if ($isNew)
		{
			// For new records, check the create permission.
			if ($this->canDo->get('core.create')) 
			{
				ToolbarHelper::apply('event.apply', 'JTOOLBAR_APPLY');
				ToolbarHelper::save('event.save', 'JTOOLBAR_SAVE');
			}
			ToolbarHelper::cancel('event.cancel', 'JTOOLBAR_CANCEL');
		}
		else
		{
			if ($this->canDo->get('core.edit'))
			{
				// We can save the new record
				ToolbarHelper::apply('event.apply', 'JTOOLBAR_APPLY');
				ToolbarHelper::save('event.save', 'JTOOLBAR_SAVE');
 
				// We can save this record, but check the create permission to see
				// if we can return to make a new one.
				if ($this->canDo->get('core.create')) 
				{
					ToolbarHelper::custom('event.save2new', 'save-new.png', 'save-new_f2.png',
					                       'JTOOLBAR_SAVE_AND_NEW', false);
				}
			}
			ToolbarHelper::cancel('event.cancel', 'JTOOLBAR_CLOSE');
		}
	}
	/**
	 * Method to set up the document properties
	 */
	protected function setupDocument() 
	{
		$isNew = ($this->item->id < 1);
		$this->document->setTitle($isNew ? Text::_('COM_U3ABOOKING_EVENT_CREATING') :
                Text::_('COM_U3ABOOKING_EVENT_EDITING'));
	}
}