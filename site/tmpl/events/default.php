<?php
/**
 * Layout file for the Site view which displays the Events
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted Access');

use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;

?>
<?php if (empty($this->items)) : ?>
	<h1>No events currently scheduled</h1>
<?php else: ?>
	<h1>Events currently scheduled</h1>
	<p>Click on the event you want to book places on.</p>
	<table class="table table-striped table-hover u3a-events-table" id="eventList">
		<thead>
		<tr>
			<th width="30%">
				<?php echo "Date and Time"; ?>
			</th>
			<th width="30%">
				<?php echo "Event"; ?>
			</th>
			<th width="24%">
				<?php echo "Venue"; ?>
			</th>
			<th width="8%">
				<?php echo "Capacity"; ?>
			</th>
			<th width="8%">
				<?php echo "Places remaining"; ?>
			</th>
		</tr>
		</thead>
		<tbody>
			<?php foreach ($this->items as $i => $row) : ?>
		<tr>
			<td align="center">
				<?php $event_start_timestamp = strtotime($row->event_start); ?>
				<?php $event_end_timestamp = strtotime($row->event_end); ?>
				<?php $startDate = date("D,j M Y ", $event_start_timestamp); ?>
				<?php $startTime = date("H:i", $event_start_timestamp); ?>
				<?php $endDate = date("D,j M Y ", $event_end_timestamp); ?>
				<?php $endTime = date("H:i", $event_end_timestamp); ?>
				<?php $sameDate = ($startDate == $endDate); ?>
				<?php echo $startDate . $startTime . ' - ' . ($sameDate ? $endTime : $endDate . $endTime); ?>
			</td>
			<td align="center">
				<?php $link = Route::_('index.php?option=com_u3abooking&view=booking&layout=add&id=0&eventid=' . $row->id . ':' . $row->alias); ?>
				<a href="<?php echo $link; ?>" title="Click to book places on this event">
                    <?php echo $row->title; ?>
                </a>
			</td>
			<td align="center">
				<?php echo $row->venue; ?>
			</td>
			<td align="center">
				<?php echo $row->capacity; ?>
			</td>
			<td align="center">
				<?php echo $row->capacity - $row->tickets_taken; ?>
			</td>
			<?php endforeach; ?>
		<tr>
		</tbody>
	</table>
<?php endif; ?>
