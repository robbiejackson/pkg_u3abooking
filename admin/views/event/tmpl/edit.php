<?php
/**
 * Layout for the admin form for creating or editing an Event 
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;

JHtml::_('behavior.formvalidator');

// submit button to do any javascript validation (tbc) before submitting the form
Factory::getDocument()->addScriptDeclaration("
Joomla.submitbutton = function(task)
{
	if (task == '')
	{
		return false;
	}
	else
	{
		var isValid=true;
		var action = task.split('.');
		if (action[1] != 'cancel' && action[1] != 'close')
		{
			var forms = jQuery('form.form-validate');
			for (var i = 0; i < forms.length; i++)
			{
				if (!document.formvalidator.isValid(forms[i]))
				{
					isValid = false;
					break;
				}
			}
		}
		if (isValid)
		{
			Joomla.submitform(task);
			return true;
		}
		else
		{
			alert(Joomla.JText._('COM_U3ABOOKING_EVENT_ERROR_UNACCEPTABLE',
			                     'Some values are unacceptable'));
			return false;
		}
	}
}
");

// if &tmpl=component used on first invocation, ensure it's on subsequent ones too
$input = JFactory::getApplication()->input;
$tmpl = $input->getCmd('tmpl', '') === 'component' ? '&tmpl=component' : '';
?>
<form action="<?php echo JRoute::_('index.php?option=com_u3abooking&layout=edit' . $tmpl . '&id=' . (int) $this->item->id); ?>"
    method="post" name="adminForm" id="adminForm" class="form-validate">
    
    <div class="form-horizontal">

	<?php echo JLayoutHelper::render('joomla.edit.title_alias', $this); ?>
	
    <?php echo JHtml::_('bootstrap.startTabSet', 'myTab', array('active' => 'details')); ?>
    <?php echo JHtml::_('bootstrap.addTab', 'myTab', 'details', JText::_('COM_U3ABOOKING_EVENT_DETAILS')); ?>
        <fieldset class="adminform">
            <div class="row-fluid">
                <div class="span3">
                    <?php echo $this->form->renderFieldset('details');  ?>
					<?php $url = Uri::root() . "component/u3abooking/?view=booking&layout=add&eventid={$this->item->id}";  ?>
					<?php echo "Booking URL: <a href=\"{$url}\">URL</a>";  ?>
                </div>
				<div class="span9">
                    <?php echo $this->form->getInput('description');  ?>
                </div>
            </div>
        </fieldset>
    <?php echo JHtml::_('bootstrap.endTab'); ?>

    <?php echo JHtml::_('bootstrap.endTabSet'); ?>

    </div>
	
    <input type="hidden" name="task" value="event.edit" />
    <?php echo JHtml::_('form.token'); ?>
</form>