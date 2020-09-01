<?php
/**
 * Layout file for the Admin view which displays the Bookings
 */

// No direct access to this file
defined('_JEXEC') or die('Restricted Access');

use Joomla\Registry\Registry;
use Joomla\CMS\Factory;

JHtml::_('formbehavior.chosen', 'select');

$listOrder     = $this->escape($this->state->get('list.ordering'));
$listDirn      = $this->escape($this->state->get('list.direction'));
$user = Factory::getUser();
$userId = $user->get('id');
?>
<form action="index.php?option=com_u3abooking&view=bookings" method="post" id="adminForm" name="adminForm">
	<div id="j-sidebar-container" class="span2">
		<?php echo JHtmlSidebar::render(); ?>
	</div>
	<div id="j-main-container" class="span10">
        <div class="row-fluid">
            <div class="span10">
                <?php echo JText::_('COM_U3ABOOKING_FILTERS'); ?>
                <?php
                    echo JLayoutHelper::render(
                        'joomla.searchtools.default',
                        array('view' => $this)
                    );
                ?>
            </div>
        </div>
        <table class="table table-striped table-hover" id="eventList">
            <thead>
            <tr>
                <th width="1%"><?php echo JText::_('COM_U3ABOOKING_EVENTS_NUM'); ?></th>
                <th width="2%">
                    <?php echo JHtml::_('grid.checkall'); ?>
                </th>
                <th width="8%">
                    <?php echo JHtml::_('searchtools.sort',  'COM_U3ABOOKING_BOOKINGS_BOOKING_REFERENCE', 'booking_reference', $listDirn, $listOrder); ?>
                </th>
                <th width="24%">
                    <?php echo JHtml::_('searchtools.sort', 'COM_U3ABOOKING_BOOKINGS_EVENT', 'event_title', $listDirn, $listOrder); ?>
                </th>
				<th width="15%">
                    <?php echo JHtml::_('searchtools.sort', 'COM_U3ABOOKING_BOOKINGS_EVENT_START', 'event_start', $listDirn, $listOrder); ?>
                </th>
                <th width="5%">
                    <?php echo JHtml::_('searchtools.sort',  'COM_U3ABOOKING_BOOKINGS_NUM_TICKETS', 'num_tickets', $listDirn, $listOrder); ?>
                </th>
				<th width="10%">
                    <?php echo JHtml::_('searchtools.sort',  'COM_U3ABOOKING_BOOKINGS_TELEPHONE', 'telephone', $listDirn, $listOrder); ?>
                </th>
                <th width="15%">
                    <?php echo JHtml::_('searchtools.sort',  'COM_U3ABOOKING_BOOKINGS_EMAIL', 'email', $listDirn, $listOrder); ?>
                </th>
                <th width="8%">
                    <?php echo JHtml::_('searchtools.sort', 'COM_U3ABOOKING_BOOKINGS_USERNAME', 'username', $listDirn, $listOrder); ?>
                </th>
                <th width="10%">
                    <?php echo JHtml::_('searchtools.sort', 'COM_U3ABOOKING_BOOKINGS_CREATED_DATE', 'created', $listDirn, $listOrder); ?>
                    </th>
                <th width="2%">
                    <?php echo JHtml::_('searchtools.sort', 'COM_U3ABOOKING_BOOKINGS_ID', 'id', $listDirn, $listOrder); ?>
                </th>
            </tr>
            </thead>
            <tfoot>
                <tr>
                    <td colspan="5">
                        <?php echo $this->pagination->getListFooter(); ?>
                    </td>
                </tr>
            </tfoot>
            <tbody>
                <?php if (!empty($this->items)) : ?>
                    <?php foreach ($this->items as $i => $row) :
                        $link = JRoute::_('index.php?option=com_u3abooking&task=booking.edit&id=' . $row->id);
                    ?>
                        <tr>
                            <td><?php echo $this->pagination->getRowOffset($i); ?></td>
                            <td>
                                <?php echo JHtml::_('grid.id', $i, $row->id); ?>
                            </td>
							<td align="center">
								<a href="<?php echo $link; ?>" title="<?php echo JText::_('COM_U3ABOOKING_EDIT_BOOKING'); ?>">
                                    <?php echo $row->booking_reference; ?>
                                </a>
                            </td>
                            <td align="center">
                                <?php $eventLink = JRoute::_('index.php?option=com_u3abooking&task=event.edit&id=' . $row->event_id); ?>
								<a href="<?php echo $eventLink; ?>" title="<?php echo JText::_('COM_U3ABOOKING_EDIT_EVENT'); ?>">
                                    <?php echo $row->event_title; ?>
                                </a>
                            </td>
							<td align="center">
                                <?php $sameDate = (substr($row->event_start, 0, 10) == substr($row->event_end, 0, 10)); ?>
								<?php echo substr($row->event_start, 0, 16) . ' - ' . ($sameDate ? substr($row->event_end, 11, 5) : substr($row->event_end, 0, 16)); ?>
                            </td>
							<td align="center">
                                <?php echo $row->num_tickets; ?>
                            </td>
							<td align="center">
                                <?php echo $row->telephone; ?>
                            </td>
							<td align="center">
                                <?php echo $row->email; ?>
                            </td>
							<td align="center">
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
        <?php // load the modal for displaying the batch options
            //echo JHtml::_(
            //'bootstrap.renderModal',
            //'collapseModal',
            //array(
            //    'title' => JText::_('COM_HELLOWORLD_BATCH_OPTIONS'),
            //    'footer' => $this->loadTemplate('batch_footer')
            //),
            //$this->loadTemplate('batch_body')
        //); 
		?>
        <input type="hidden" name="task" value=""/>
        <input type="hidden" name="boxchecked" value="0"/>
        <?php echo JHtml::_('form.token'); ?>
    </div>
</form>