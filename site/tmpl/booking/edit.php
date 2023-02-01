<?php
/**
 * Layout for displaying the event and allowing booking against it
 */
 
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

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
	<?php echo "Remaining places available: $remainingPlaces"; ?>
	<?php $nplaces = $this->event->max_tickets_per_booking == 1 ? "1 place" : $this->event->max_tickets_per_booking . " places"; ?>
	<?php echo "You may book at most $nplaces at this event<br>"; ?> 
</p>

<?php 
	$app = Factory::getApplication();
	$bookingref = $app->input->getString('booking', '');
	$bookingParam = $bookingref ? "&booking=" . $bookingref : "";
	$return = $app->input->get('return', '', 'base64');
	$returnParam = $return ? "&return=" . $return : "";
?>
	
<form action="<?php echo Route::_("index.php?option=com_u3abooking&view=booking&layout=edit&eventid=" . $this->event->id . ':' . $this->event->alias . "&id=" . $this->booking->id . $bookingParam . $returnParam); ?>"
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
				<span class="icon-ok"></span><?php echo Text::_('COM_U3ABOOKING_AMEND_BOOKING') ?>
			</button>
		</div>
		<div class="btn-group">
			<button type="button" class="btn btn-primary" onclick="Joomla.submitbutton('booking.delete', '', false)">
				<span class="icon-ok"></span><?php echo Text::_('COM_U3ABOOKING_DELETE_BOOKING') ?>
			</button>
		</div>
		<div class="btn-group">
			<button type="button" class="btn" onclick="Joomla.submitbutton('booking.cancelAmend', '', false)">
				<span class="icon-cancel"></span><?php echo Text::_('COM_U3ABOOKING_CANCEL_AMEND_BOOKING') ?>
			</button>
		</div>
	</div>

	<input type="hidden" name="task" />
	<?php echo HTMLHelper::_('form.token'); ?>
</form>
<?php endif; ?>
