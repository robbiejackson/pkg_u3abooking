<?php
/**
 * Layout for displaying the event and allowing booking against it
 */
 
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;

$this->document->getWebAssetManager()->useScript('form.validate');

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
	<?php echo "Remaining places available: $remainingPlaces<br>"; ?>
	<?php $nplaces = $this->event->max_tickets_per_booking == 1 ? "1 place" : $this->event->max_tickets_per_booking . " places"; ?>
	<?php echo "You may book at most $nplaces at this event<br>"; ?> 
</p>
<form action="<?php echo Route::_("index.php?option=com_u3abooking&view=booking&layout=add&id=0&eventid=" . $this->event->id); ?>"
    method="post" name="adminForm" id="adminForm" class="form-validate">
<div style="border: solid 2px black;padding: 5px">
	<div class="form-horizontal">
		<fieldset class="adminform">
			<legend><?php echo Text::_('COM_U3ABOOKING_AMEND_EXISTING_BOOKING'); ?></legend>
			<div class="row-fluid">
				<div class="span12">
					<?php echo $this->form->renderField('booking_ref_for_amendment');  ?>
				</div>
			</div>
		</fieldset>
	</div>
	<div class="btn-toolbar">
		<div class="btn-group">
			<button type="button" class="btn btn-primary" onclick="Joomla.submitbutton('booking.find', '', false)">
				<span class="icon-ok"></span><?php echo Text::_('COM_U3ABOOKING_FIND_BOOKING') ?>
			</button>
		</div>
		</div>
</div>
<br>
<div style="border: solid 2px black;padding: 5px">
	<div class="form-horizontal">
		<fieldset class="adminform">
			<legend><?php echo Text::_('COM_U3ABOOKING_MAKE_NEW_BOOKING'); ?></legend>
			<div class="row-fluid">
				<div class="span12">
					<?php echo $this->form->renderFieldset('booking_details');  ?>
				</div>
			</div>
		</fieldset>
	</div>
	<div class="btn-toolbar">
		<div class="btn-group">
			<button type="button" class="btn btn-primary" onclick="Joomla.submitbutton('booking.add')">
				<span class="icon-ok"></span><?php echo Text::_('COM_U3ABOOKING_ADD_BOOKING') ?>
			</button>
		</div>
		<div class="btn-group">
			<button type="button" class="btn" onclick="Joomla.submitbutton('booking.cancelAdd', '', false)">
				<span class="icon-cancel"></span><?php echo Text::_('JCANCEL') ?>
			</button>
		</div>
	</div>

	<input type="hidden" name="task" />
	<?php echo HTMLHelper::_('form.token'); ?>
</div>
</form>
<?php endif; ?>
