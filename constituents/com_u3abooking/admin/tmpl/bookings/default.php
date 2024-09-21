<?php
/**
 * Layout file for the Admin view which displays the Bookings
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted Access');

use Joomla\Registry\Registry;
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;

$listOrder     = $this->escape($this->state->get('list.ordering'));
$listDirn      = $this->escape($this->state->get('list.direction'));
?>
<form action="index.php?option=com_u3abooking&view=bookings" method="post" id="adminForm" name="adminForm">
	<div class="row">
        <div class="col-md-12">
            <div id="j-main-container" class="j-main-container">
                <?php
                    echo LayoutHelper::render(
                        'joomla.searchtools.default',
                        array('view' => $this)
                    );
                ?>
                <table class="table table-striped table-hover" id="eventList">
                    <thead>
                    <tr>
                        <th width="1%"><?php echo Text::_('COM_U3ABOOKING_EVENTS_NUM'); ?></th>
                        <th width="2%">
                            <?php echo HTMLHelper::_('grid.checkall'); ?>
                        </th>
                        <th width="8%">
                            <?php echo HTMLHelper::_('searchtools.sort',  'COM_U3ABOOKING_BOOKINGS_BOOKING_REFERENCE', 'booking_reference', $listDirn, $listOrder); ?>
                        </th>
                        <th width="24%">
                            <?php echo HTMLHelper::_('searchtools.sort', 'COM_U3ABOOKING_BOOKINGS_EVENT', 'event_title', $listDirn, $listOrder); ?>
                        </th>
                        <th width="15%">
                            <?php echo HTMLHelper::_('searchtools.sort', 'COM_U3ABOOKING_BOOKINGS_EVENT_START', 'event_start', $listDirn, $listOrder); ?>
                        </th>
                        <th width="5%">
                            <?php echo HTMLHelper::_('searchtools.sort',  'COM_U3ABOOKING_BOOKINGS_NUM_TICKETS', 'num_tickets', $listDirn, $listOrder); ?>
                        </th>
                        <th width="10%">
                            <?php echo HTMLHelper::_('searchtools.sort',  'COM_U3ABOOKING_BOOKINGS_TELEPHONE', 'telephone', $listDirn, $listOrder); ?>
                        </th>
                        <th width="15%">
                            <?php echo HTMLHelper::_('searchtools.sort',  'COM_U3ABOOKING_BOOKINGS_EMAIL', 'email', $listDirn, $listOrder); ?>
                        </th>
                        <th width="8%">
                            <?php echo HTMLHelper::_('searchtools.sort', 'COM_U3ABOOKING_BOOKINGS_USERNAME', 'username', $listDirn, $listOrder); ?>
                        </th>
                        <th width="10%">
                            <?php echo HTMLHelper::_('searchtools.sort', 'COM_U3ABOOKING_BOOKINGS_CREATED_DATE', 'created', $listDirn, $listOrder); ?>
                            </th>
                        <th width="2%">
                            <?php echo HTMLHelper::_('searchtools.sort', 'COM_U3ABOOKING_BOOKINGS_ID', 'id', $listDirn, $listOrder); ?>
                        </th>
                    </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($this->items)) : ?>
                            <?php foreach ($this->items as $i => $row) :
                                $link = Route::_('index.php?option=com_u3abooking&task=booking.edit&id=' . $row->id);
                            ?>
                                <tr>
                                    <td><?php echo $this->pagination->getRowOffset($i); ?></td>
                                    <td>
                                        <?php echo HTMLHelper::_('grid.id', $i, $row->id); ?>
                                    </td>
                                    <td>
                                        <a href="<?php echo $link; ?>" title="<?php echo Text::_('COM_U3ABOOKING_EDIT_BOOKING'); ?>">
                                            <?php echo $row->booking_reference; ?>
                                        </a>
                                    </td>
                                    <td>
                                        <?php $eventLink = Route::_('index.php?option=com_u3abooking&task=event.edit&id=' . $row->event_id); ?>
                                        <a href="<?php echo $eventLink; ?>" title="<?php echo Text::_('COM_U3ABOOKING_EDIT_EVENT'); ?>">
                                            <?php echo $row->event_title; ?>
                                        </a>
                                    </td>
                                    <td>
                                        <?php $sameDate = (substr($row->event_start, 0, 10) == substr($row->event_end, 0, 10)); ?>
                                        <?php echo substr($row->event_start, 0, 16) . ' - ' . ($sameDate ? substr($row->event_end, 11, 5) : substr($row->event_end, 0, 16)); ?>
                                    </td>
                                    <td align="center">
                                        <?php echo $row->num_tickets; ?>
                                    </td>
                                    <td align="center">
                                        <?php echo $row->telephone; ?>
                                    </td>
                                    <td>
                                        <?php echo $row->email; ?>
                                    </td>
                                    <td>
                                        <?php echo $row->username; ?>
                                    </td>
                                    <td align="center">
                                        <?php echo substr($row->created, 0, 16); ?>
                                    </td>
                                    <td align="center">
                                        <?php echo $row->id; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
                <input type="hidden" name="task" value=""/>
                <input type="hidden" name="boxchecked" value="0"/>
                <?php echo HTMLHelper::_('form.token'); ?>
                <?php echo $this->pagination->getListFooter(); ?>
            </div>
        </div>
    </div>
</form>