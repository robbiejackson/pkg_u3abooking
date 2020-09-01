<?php
/**
 * Layout for displaying the event and allowing booking against it
 */
 
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Factory;

JHtml::_('behavior.formvalidator');
// submit button to do any javascript validation before submitting the form
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
		if (action[1] != 'cancelAmend' && action[1] != 'close')
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
			alert(Joomla.JText._('COM_U3ABOOKING_BOOKING_ERROR_UNACCEPTABLE',
			                     'Some values are unacceptable'));
			return false;
		}
	}
}
");

?>
<?php if($this->event): ?>
<h1>
	<?php echo $this->event->title; ?>
</h1>
<p>
	<?php echo $this->event->description; ?>
</p>
<p>
	<?php $remainingPlaces = $this->event->capacity - $this->event->tickets_taken; ?>
	<?php echo "Remaining places available: $remainingPlaces"; ?>
	<?php $nplaces = $this->event->max_tickets_per_booking == 1 ? "1 place" : $this->event->max_tickets_per_booking . " places"; ?>
	<?php echo "You may book at most $nplaces at this event<br>"; ?> 
</p>

<?php 
	$app = Factory::getApplication();
	$bookingref = $app->input->getString('booking', '');
	$bookingParam = $bookingref ? "&booking=" . $bookingref : "";
	$return = $app->input->getCmd('return', '');
	$returnParam = $return ? "&return=" . $return : "";
?>
	
<form action="<?php echo JRoute::_("index.php?option=com_u3abooking&view=booking&layout=edit&eventid=" . $this->event->id . "&id=" . $this->booking->id . $bookingParam . $returnParam); ?>"
    method="post" name="adminForm" id="adminForm" class="form-validate">

	<div class="form-horizontal">
		<fieldset class="adminform">
			<legend><?php echo "Event Booking"; ?></legend>
			<div class="row-fluid">
				<div class="span12">
					<?php echo $this->form->renderField('booking_ref');  ?>
				</div>
				<div class="span12">
					<?php echo $this->form->renderFieldset('booking_details');  ?>
				</div>
			</div>
		</fieldset>
	</div>
	<div class="btn-toolbar">
		<div class="btn-group">
			<button type="button" class="btn btn-primary" onclick="Joomla.submitbutton('booking.amend')">
				<span class="icon-ok"></span><?php echo JText::_('COM_U3ABOOKING_AMEND_BOOKING') ?>
			</button>
		</div>
		<div class="btn-group">
			<button type="button" class="btn btn-primary" onclick="Joomla.submitbutton('booking.delete')">
				<span class="icon-ok"></span><?php echo JText::_('COM_U3ABOOKING_DELETE_BOOKING') ?>
			</button>
		</div>
		<div class="btn-group">
			<button type="button" class="btn" onclick="Joomla.submitbutton('booking.cancelAmend')">
				<span class="icon-cancel"></span><?php echo JText::_('COM_U3ABOOKING_CANCEL_AMEND_BOOKING') ?>
			</button>
		</div>
	</div>

	<input type="hidden" name="task" />
	<?php echo JHtml::_('form.token'); ?>
</form>
<?php endif; ?>
