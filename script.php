<?php
defined('_JEXEC') or die();

use Joomla\CMS\Installer\InstallerScript;
use Joomla\CMS\Table\Table;

class com_U3ABookingInstallerScript extends InstallerScript
{
	public function update($parent) 
    {
        echo '<p>' . JText::sprintf('COM_U3ABOOKING_UPDATE_TEXT', $parent->get('manifest')->version) . '</p>';
    }

	public function postflight($type, $parent)
	{
		if ($type == 'install' || $type == 'discover_install')
		{
			echo '<p>Inserting default category record</p>';
			// Create default category of "Uncategorised"
			$category              = Table::getInstance('Category');
			$category->extension   = 'com_u3abooking';
			$category->title       = 'Uncategorised';
			$category->alias       = 'uncategorised';
			$category->description = '';
			$category->published   = 1;
			$category->access      = 1;
			$category->language    = '*';
			$category->setLocation(1, 'last-child');
			$category->store(true);
			$category->rebuildPath($category->id);
		}
	}
}