<?php
namespace Robbie\Component\U3ABooking\Administrator\Rule;

// No direct access to this file
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Form\FormRule;

/**
 * Form Rule class for the Joomla Framework.
 * Used to check that a booking reference part starts with a /
 */
class BookingrefpartRule extends FormRule
{
	/**
	 * The regular expression.
	 *
	 * @access	protected
	 * @var		string
	 * @since	2.5
	 */
	protected $regex = '^\/.+$';
}