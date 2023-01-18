<?php
namespace Robbie\Component\U3ABooking\Administrator\Model;

defined('_JEXEC') or die('Restricted access');

use Joomla\Registry\Registry;
use Joomla\CMS\MVC\Model\AdminModel;
use Joomla\CMS\Factory;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Log\Log;

/**
 * Model which handles actions in the Admin Events form and Admin Edit Event
 *
 */
class EventModel extends AdminModel
{

	/**
	 * Method to get the record form.
	 *
	 */
	public function getForm($data = array(), $loadData = true)
	{
		$form = $this->loadForm(
			'com_u3abooking.event',
			'event',
			array(
				'control' => 'jform',
				'load_data' => $loadData
			)
		);
		if (empty($form))
		{
			return false;
		}
		return $form;
	}
 	
	/**
	 * Method to get the data that should be injected in the form.
	 *
	 */
	protected function loadFormData()
	{
		// get data which the user previously entered into the form
		// the context 'com_u3abooking.edit.event.data' is set in FormController
		$data = Factory::getApplication()->getUserState(
			'com_u3abooking.edit.event.data',
			array()
		);
		if (empty($data))
		{
			$data = $this->getItem();
		}

		return $data;
	}
	
	public function validate($form, $data, $group = null)
	{
		if ($data['event_start'] >= $data['event_end'])
		{
			$this->setError("Event end date/time must be after Event start date/time");
			return false;
		}

		return parent::validate($form, $data, $group);
	}
	
	/**
	 * Prepare a record for saving in the database 
	 * We just use this to set a new record with 'ordering' set to the end
	 */
	protected function prepareTable($table)
	{
		// Set ordering to the last item if not set
		if (empty($table->ordering))
		{
			$db = $this->getDbo();
			$query = $db->getQuery(true)
				->select('MAX(ordering)')
				->from('#__u3a_event');

			$db->setQuery($query);
			$max = $db->loadResult();

			$table->ordering = $max + 1;
		}
	}

}