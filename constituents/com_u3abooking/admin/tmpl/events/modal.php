<?php
/**
 * Layout file for the admin modal display of event records
 *
 */

defined('_JEXEC') or die('Restricted Access');

use Joomla\Registry\Registry;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Session\Session;

$this->document->getWebAssetManager()->useScript('modal-content-select');

$listOrder     = $this->escape($this->state->get('list.ordering'));
$listDirn      = $this->escape($this->state->get('list.direction'));

$app = Factory::getApplication();
$onclick   = $this->escape($function);
?>
<div class="container-popup">
    
<form action="<?php echo Route::_('index.php?option=com_u3abooking&view=events&layout=modal&tmpl=component&' . Session::getFormToken() . '=1'); ?>" method="post" name="adminForm" id="adminForm" class="form-inline">

    <?php echo LayoutHelper::render('joomla.searchtools.default', array('view' => $this)); ?>
    
    <div class="clearfix"></div>

        <table class="table table-striped table-hover">
            <thead>
            <tr>
                <th width="3%"><?php echo Text::_('COM_U3ABOOKING_EVENTS_NUM'); ?></th>
                <th width="25%">
                    <?php echo HTMLHelper::_('searchtools.sort', 'COM_U3ABOOKING_EVENTS_TITLE', 'title', $listDirn, $listOrder); ?>
                </th>
                <th width="15%">
                    <?php echo HTMLHelper::_('searchtools.sort', 'COM_U3ABOOKING_EVENTS_VENUE', 'venue', $listDirn, $listOrder); ?>
                </th>
                <th width="20%">
                    <?php echo HTMLHelper::_('searchtools.sort', 'COM_U3ABOOKING_EVENTS_DATETIME', 'event_start', $listDirn, $listOrder); ?>
                </th>
                <th width="6%">
                    <?php echo Text::_('COM_U3ABOOKING_EVENTS_CAPACITY'); ?>
                </th>
                <th width="6%">
                    <?php echo Text::_('JCATEGORY'); ?>
                </th>
                <th width="6%">
                    <?php echo HTMLHelper::_('searchtools.sort',  'JGRID_HEADING_ACCESS', 'access', $listDirn, $listOrder); ?>
                </th>
                <th width="6%">
                    <?php echo HTMLHelper::_('searchtools.sort', 'COM_U3ABOOKING_EVENTS_ORGANISER', 'organiser', $listDirn, $listOrder); ?>
                </th>
                <th width="6%">
                    <?php echo HTMLHelper::_('searchtools.sort', 'COM_U3ABOOKING_EVENTS_CREATED_DATE', 'created', $listDirn, $listOrder); ?>
                    </th>
                <th width="5%">
                    <?php echo Text::_('COM_U3ABOOKING_EVENTS_STATE'); ?>
                </th>
                <th width="2%">
                    <?php echo HTMLHelper::_('searchtools.sort', 'COM_U3ABOOKING_EVENTS_ID', 'id', $listDirn, $listOrder); ?>
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
                    <?php foreach ($this->items as $i => $row) : ?>
                        <tr>
                            <td><?php echo $this->pagination->getRowOffset($i); ?></td>
                            <td>
                                <?php 
                                $link = 'index.php?option=com_u3abooking&view=event&id=' . $row->id;
                                $attribs = 'data-content-select'
                                . ' data-id="' . $row->id . '"'
                                . ' data-title="' . $this->escape(addslashes($row->title)) . '"'
                                ;
                                ?>
                                <a class="select-link" href="javascript:void(0)" <?php echo $attribs; ?>>
                                    <?php echo $this->escape($row->title); ?>
                                </a>
                                <span class="small break-word">
                                    <?php echo Text::sprintf('JGLOBAL_LIST_ALIAS', $this->escape($row->alias)); ?>
                                </span>
                            </td>
                            <td align="center">
                                <?php echo $row->venue; ?>
                            </td>
                            <td align="center">
                                <?php $sameDate = (substr($row->event_start, 0, 10) == substr($row->event_end, 0, 10)); ?>
                                <?php echo substr($row->event_start, 0, 16) . ' - ' . ($sameDate ? substr($row->event_end, 11, 5) : substr($row->event_end, 0, 16)); ?>
                            </td>
                            <td align="center">
                                <?php echo $row->capacity; ?>
                            </td>
                            <td align="center">
                                <?php echo $row->category_title; ?>
                            </td>
                            <td align="center">
                                <?php echo $row->access_level; ?>
                            </td>
                            <td align="center">
                                <?php echo $row->organiser; ?>
                            </td>
                            <td align="center">
                                <?php echo substr($row->created, 0, 10); ?>
                            </td>
                            <td align="center">
                                <?php echo HTMLHelper::_('jgrid.published', $row->published, $i, 'events.', true, 'cb'); ?>
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
</form>
</div>