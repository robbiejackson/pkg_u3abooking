<?php
/**
 * Layout for the admin form for creating or editing an Event 
 */

defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Router\Route;

$this->document->getWebAssetManager()->useScript('form.validate');

// if &tmpl=component used on first invocation, ensure it's on subsequent ones too
$input = Factory::getApplication()->input;
$tmpl = $input->getCmd('tmpl', '') === 'component' ? '&tmpl=component' : '';
?>
<form action="<?php echo Route::_('index.php?option=com_u3abooking&layout=edit' . $tmpl . '&id=' . (int) $this->item->id); ?>"
    method="post" name="adminForm" id="adminForm" class="form-validate">
    
    <div class="form-horizontal">

        <?php echo LayoutHelper::render('joomla.edit.title_alias', $this); ?>
	
        <fieldset class="adminform">
            <div class="row">
                <div class="col-lg-9">
                    <?php echo $this->form->getInput('description');  ?>
                </div>
                <div class="col-lg-3">
                    <?php echo $this->form->renderFieldset('details');  ?>
					<?php $url = Uri::root() . "component/u3abooking/?view=booking&layout=add&eventid={$this->item->id}";  ?>
					<?php echo "Booking URL: <a href=\"{$url}\">URL</a>";  ?>
                </div>
            </div>
        </fieldset>

    </div>
	
    <input type="hidden" name="task" value="" />
    <?php echo HTMLHelper::_('form.token'); ?>
</form>