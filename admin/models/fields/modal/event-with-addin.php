<?php

defined('JPATH_BASE') or die;

use Joomla\CMS\Factory;

/**
 * Supports a modal for selecting an event
 *
 */
class JFormFieldModal_Event extends JFormField
{
	/**
	 * Method to get the html for the input field.
	 *
	 * @return  string  The field input html.
	 */
	protected function getInput()
	{
		// Load language
		JFactory::getLanguage()->load('com_u3abooking', JPATH_ADMINISTRATOR);
		
		$app = Factory::getApplication();
		$option = $app->input->get('option');
		$u3a = ($option == 'com_u3abooking');

		// $this->value is set if there's a default id specified in the xml file
		$value = (int) $this->value > 0 ? (int) $this->value : '';
        
		// $this->id will be jform_request_xxx where xxx is the name of the field in the xml file
		// or jform_associations_xx_yy where xx_yy is the language code (hyphen replaced by underscore) for associations
		$modalId = 'Event_' . $this->id;

		// Add the modal field script to the document head.
		JHtml::_('jquery.framework');
		JHtml::_('script', 'system/modal-fields.js', array('version' => 'auto', 'relative' => true));

		// our callback function from the modal to the main window:
		$u3aAddin = $u3a ? "let element = window.parent.document.getElementById('filter_event_id_id');
							element.dispatchEvent(new Event('change'));" : "";
		JFactory::getDocument()->addScriptDeclaration("
			function jSelectEvent_" . $this->id . "(id, title, catid, object, url, language) {
                window.processModalSelect('Event', '" . $this->id . "', id, title, catid, object, url, language);" . $u3aAddin . "
			}
			");

		// if a default id is set, then get the corresponding title to display it
		if ($value)
		{
			$db    = JFactory::getDbo();
			$query = $db->getQuery(true)
				->select($db->quoteName('title'))
				->from($db->quoteName('#__u3a_event'))
				->where($db->quoteName('id') . ' = ' . (int) $value);
			$db->setQuery($query);

			try
			{
				$title = $db->loadResult();
			}
			catch (RuntimeException $e)
			{
				JError::raiseWarning(500, $e->getMessage());
			}
		}
        
		// display the default title or "Select" if no default specified
		$title = empty($title) ? JText::_('COM_U3ABOOKING_MENUITEM_SELECT_EVENT') : htmlspecialchars($title, ENT_QUOTES, 'UTF-8');
		$html  = '<span class="input-append">';
		$html .= '<input class="input-medium" id="' . $this->id . '_name" type="text" value="' . $title . '" disabled="disabled" size="35" />';

		// html for the Select button
		$html .= '<a'
			. ' class="btn hasTooltip' . ($value ? ' hidden' : '') . '"'
			. ' id="' . $this->id . '_select"'
			. ' data-toggle="modal"'
			. ' role="button"'
			. ' href="#ModalSelect' . $modalId . '"'
			. ' title="' . JHtml::tooltipText('COM_U3ABOOKING_MENUITEM_SELECT_BUTTON_TOOLTIP') . '">'
			. '<span class="icon-file" aria-hidden="true"></span> ' . JText::_('JSELECT')
			. '</a>';

		// html for the Clear button
		$html .= '<a'
			. ' class="btn' . ($value ? '' : ' hidden') . '"'
			. ' id="' . $this->id . '_clear"'
			. ' href="#"'
			. ' onclick="window.processModalParent(\'' . $this->id . '\'); return false;">'
			. '<span class="icon-remove" aria-hidden="true"></span>' . JText::_('JCLEAR')
			. '</a>';

		$html .= '</span>';

		// url for the iframe
		$linkEvents = 'index.php?option=com_u3abooking&amp;view=events&amp;layout=modal&amp;tmpl=component&amp;' . JSession::getFormToken() . '=1';
        $urlSelect = $linkEvents . '&amp;function=jSelectEvent_' . $this->id;
        
		// title to go in the modal header
		$modalTitle    = JText::_('COM_U3ABOOKING_MENUITEM_SELECT_MODAL_TITLE');
        
        // if the form definition has a 'language' field then it's for the association
		// add the forcedLanguage parameter to the URL, and add the language to the modal title
		/*
		if (isset($this->element['language']))
		{
			$urlSelect .= '&amp;forcedLanguage=' . $this->element['language'];
			$modalTitle .= ' &#8212; ' . $this->element['label'];
		}
		*/
        
		// html to set up the modal iframe
		$html .= JHtml::_(
			'bootstrap.renderModal',
			'ModalSelect' . $modalId,
			array(
				'title'       => $modalTitle,
				'url'         => $urlSelect,
				'height'      => '400px',
				'width'       => '800px',
				'bodyHeight'  => '70',
				'modalWidth'  => '80',
				'footer'      => '<a role="button" class="btn" data-dismiss="modal" aria-hidden="true">' . JText::_('JLIB_HTML_BEHAVIOR_CLOSE') . '</a>',
			)
		);

		// class='required' for client side validation.
		$class = $this->required ? ' class="required modal-value"' : '';

		// hidden input field to store the event record id
		$u3aAddin = $u3a ? 'onchange="this.form.submit();"' : '';
		$html .= '<input type="hidden" id="' . $this->id . '_id" ' . $class 
			. ' data-required="' . (int) $this->required . '" name="' . $this->name
			. '" data-text="' . htmlspecialchars(JText::_('COM_U3ABOOKING_MENUITEM_SELECT_EVENT', true), ENT_COMPAT, 'UTF-8') 
			. '" value="' . $value . '" ' . $u3aAddin . '/>';

		return $html;
	}

	/**
	 * Method to get the html for the label field.
	 *
	 * @return  string  The field label html.
	 */
	protected function getLabel()
	{
		return str_replace($this->id, $this->id . '_id', parent::getLabel());
	}
}