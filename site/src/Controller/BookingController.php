<?php
namespace Robbie\Component\U3ABooking\Site\Controller;
/**
 * @package     Joomla.Site
 * @subpackage  com_u3abooking
 *
 * @copyright   Copyright (C) 2005 - 2015 Open Source Matters, Inc. All rights reserved.
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
// No direct access to this file
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\MVC\Controller\FormController;
use Joomla\CMS\Factory;
use Joomla\CMS\Table\Table;
use Joomla\CMS\MVC\Model\BaseDatabaseModel;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Session\Session;
use Robbie\Component\U3ABooking\Administrator\Helper\TicketReserverHelper;

/**
 * U3ABooking Controller
 *
 * @package     Joomla.Site
 * @subpackage  U3ABooking
 *
 * Used to handle the http POST from the front-end form which allows 
 * users to book against events
 *
 */
class BookingController extends FormController
{   
	public function cancel($key = null)
    {
        parent::cancel($key);
		
		$app = Factory::getApplication(); 
		
		// set up context for clearing form data
		$context = "$this->option.edit.$this->context"; 
		$app->setUserState($context . '.data', null);
        
        // set up the redirect to the site home page
        $this->setRedirect(
            (string)Uri::root(), 
            Text::_(COM_U3ABOOKING_AMENDMENT_CANCELLED)
		);
    }
	
	public function cancelAdd($key = null)
	{
        parent::cancel($key);
		
		$app = Factory::getApplication(); 
		
		// set up context for clearing form data
		$context = "$this->option.add.$this->context"; 
		$app->setUserState($context . '.data', null);
        
        // set up the redirect to the site home page
        $this->setRedirect(
            (string)Uri::root(), 
            Text::_(COM_U3ABOOKING_ADD_CANCELLED)
		);
	}
	
	public function cancelAmend($key = null)
	{
        parent::cancel($key);
		
		$app = Factory::getApplication(); 
		
		// set up context for clearing form data
		$context = "$this->option.edit.$this->context"; 
		$app->setUserState($context . '.data', null);
		
		// if there's a return parameter in the URL then use it
		$returnParam = $app->input->get('return', '', 'base64');
		if ($returnParam)
		{
			$returnUrl = base64_decode($returnParam);
		}
		else
		{	// use the site home page as the redirect URL
			$returnUrl = (string)Uri::root();
		}
        $this->setRedirect($returnUrl, Text::_(COM_U3ABOOKING_AMENDMENT_CANCELLED));
	}
	
    /*
     * Function handing the save for a new booking
     * Based on the save() function in the JControllerForm class
     */
    public function add($key = null, $urlVar = null)
    {
		// Check for request forgeries.
		$this->checkToken();
        
		$app = Factory::getApplication(); 
		$input = $app->input; 
		$model = $this->getModel('booking');
		$eventModel = $this->getModel('event');
               
		// Get the current URI to set in redirects. As we're handling a POST, 
		// this URI comes from the <form action="..."> attribute in the layout file above
		$currentUri = (string)Uri::getInstance();
       
		// get the data from the HTTP POST request
		$data  = $input->get('jform', array(), 'array');
        
		// Check that this user is allowed to book the event
		// We don't use Joomla permissions for this - we just check that the user can see the event
		$user = Factory::getUser();
		$userAccessLevels = $user->getAuthorisedViewLevels();
		
		$event = $eventModel->getTable();
		$event->load($data["event_id"]);
		
		if (!in_array($event->access, $userAccessLevels))
		{
			$app->enqueueMessage(Text::_('JERROR_ALERTNOAUTHOR'), 'error');
			$app->setHeader('status', 403, true);

			return;
		}

		// set up context for saving form data and save it
		$context = "$this->option.add.$this->context"; 
        $app->setUserState($context . '.data', $data);
        
		// Validate the posted data.
		// First we need to set up an instance of the form ...
		$form = $model->getForm($data, false);

		if (!$form)
		{
			$app->enqueueMessage($model->getError(), 'error');
			$this->setRedirect($currentUri);
			return false;
		}

		// ... and then we validate the data against it
		// The validate function called below results in the running of the validate="..." routines
		// specified against the fields in the form xml file, and also filters the data 
		// according to the filter="..." specified in the same place (removing html tags by default in strings)
		$validData = $model->validate($form, $data);

		// Handle the case where there are validation errors
		if ($validData === false)
		{
			// Get the validation messages.
			$errors = $model->getErrors();

			// Display up to three validation messages to the user.
			for ($i = 0, $n = count($errors); $i < $n && $i < 3; $i++)
			{
				if ($errors[$i] instanceof \Exception)
				{
					$app->enqueueMessage($errors[$i]->getMessage(), 'warning');
				}
				else
				{
					$app->enqueueMessage($errors[$i], 'warning');
				}
			}
			
			$this->setRedirect($currentUri);
			return false;
		}
        
		// add the 'created by' and 'created' date fields
		$validData['created_by'] = Factory::getUser()->get('id', 0);
		$now = date('Y-m-d H:i:s');
		$validData['created'] = $now;
		$seconds = substr($now, 17, 2);
		$minutes = substr($now, 14, 2);
		$validData['booking_ref_part'] = "/$seconds$minutes";
        
		// try to allocate the tickets
		$tickets_reserved = TicketReserverHelper::reserveTickets($data['event_id'], $data['num_tickets']);
		
		if ($tickets_reserved == 0)
		{
			$app->enqueueMessage("Sorry, all available places have already been taken", 'error');
			$this->setRedirect($currentUri);
			return false;
		}
		
		if ($tickets_reserved < $data['num_tickets'])
		{
			if ($tickets_reserved == 1)
			{
				$app->enqueueMessage("Sorry, only 1 place was available", 'warning');
			}
			else
			{
				$app->enqueueMessage("Sorry, only " . $tickets_reserved . " places were available", 'warning');
			}
			$validData['num_tickets'] = $tickets_reserved;
		}
		
		// Attempt to save the data.
		if (!$model->save($validData))
		{
            // Handle the case where the save failed - redirect back to the edit form
			$this->setError(Text::sprintf('JLIB_APPLICATION_ERROR_SAVE_FAILED', $model->getError()));
			$this->setMessage($this->getError(), 'error');
			$this->setRedirect($currentUri);
			
			Log::add($this->getError(), Log::ERROR, 'site-add-booking');

			return false;
		}
        
		// data has been saved ok, so clear the data in the form
		$app->setUserState($context . '.data', null);
		
		$bookingId = $model->getBookingRecordId();
		$this->sendEmail($bookingId, $validData, $event);

		$view = $this->getView('bookingconfirmation', 'html', 'site');
		$view->setModel($eventModel);
		$eventModel->setState('u3aevent.id', $event->id);
		$view->setModel($model, true);  // this is the booking model
		$model->setState('booking.id', $bookingId);
		$view->display();
		
		return true;
    }
	
	public function amend($key = null, $urlVar = null)
    {
		// Check for request forgeries.
		$this->checkToken();
        
		$app = Factory::getApplication(); 
		$input = $app->input; 
		$model = $this->getModel('booking');
		$eventModel = $this->getModel('event');
               
		// Get the current URI to set in redirects. As we're handling a POST, 
		// this URI comes from the <form action="..."> attribute in the layout file above
		$currentUri = (string)Uri::getInstance();
       
		// get the data from the HTTP POST request
		$data  = $input->get('jform', array(), 'array');
        
		// Don't bother checking the user has Access to see the event
		// The check that the booking reference matches will suffice
		$existingBooking = $model->getItem($data['id']);
		if ($data['booking_ref_part'] != $existingBooking->booking_ref_part)
		{
			$this->setRedirect($currentUri, Text::_('COM_U3ABOOKING_INVALID_BOOKING_REFERENCE'), 'error');
			return false;
		}

		// set up context for saving form data and save it
		$context = "$this->option.edit.$this->context"; 
        $app->setUserState($context . '.data', $data);
        
		// Validate the posted data.
		// First we need to set up an instance of the form ...
		$form = $model->getForm($data, false);

		if (!$form)
		{
			$app->enqueueMessage($model->getError(), 'error');
			$this->setRedirect($currentUri);
			return false;
		}

		// ... and then we validate the data against it
		// The validate function called below results in the running of the validate="..." routines
		// specified against the fields in the form xml file, and also filters the data 
		// according to the filter="..." specified in the same place (removing html tags by default in strings)
		$validData = $model->validate($form, $data);

		// Handle the case where there are validation errors
		if ($validData === false)
		{
			// Get the validation messages.
			$errors = $model->getErrors();

			// Display up to three validation messages to the user.
			for ($i = 0, $n = count($errors); $i < $n && $i < 3; $i++)
			{
				if ($errors[$i] instanceof \Exception)
				{
					$app->enqueueMessage($errors[$i]->getMessage(), 'warning');
				}
				else
				{
					$app->enqueueMessage($errors[$i], 'warning');
				}
			}
			
			$this->setRedirect($currentUri);
			return false;
		}
        
		// have to see how the number of tickets requested has changed
		if ($validData["num_tickets"] > $existingBooking->num_tickets)
		{
			// try to allocate the extra tickets
			$ticketsToReserve = $validData['num_tickets'] - $existingBooking->num_tickets;
			$tickets_reserved = TicketReserverHelper::reserveTickets($validData['event_id'], $ticketsToReserve);
			
			if ($tickets_reserved == 0)
			{
				$app->enqueueMessage("Sorry, all available places have already been taken", 'warning');
				$this->setRedirect($currentUri);
				return false;
			}
			
			if ($tickets_reserved < $ticketsToReserve)
			{
				if ($tickets_reserved == 1)
				{
					$app->enqueueMessage("Sorry, only 1 extra place was available", 'warning');
				}
				else
				{
					$app->enqueueMessage("Sorry, only " . $tickets_reserved . " extra places were available", 'warning');
				}
				$validData['num_tickets'] = $existingBooking->num_tickets + $tickets_reserved;
			}
		}
		elseif ($validData["num_tickets"] < $existingBooking->num_tickets)
		{
			TicketReserverHelper::unreserveTickets($validData['event_id'], $existingBooking->num_tickets - $validData['num_tickets']);
		}

		// Attempt to save the data.
		if (!$model->save($validData))
		{
            // Handle the case where the save failed - redirect back to the edit form
			$this->setError(Text::sprintf('JLIB_APPLICATION_ERROR_SAVE_FAILED', $model->getError()));
			$this->setMessage($this->getError(), 'error');
			$this->setRedirect($currentUri);
			
			Log::add($this->getError(), Log::ERROR, 'site-amend-booking');

			return false;
		}
        
		// data has been saved ok, so clear the data in the form
		$app->setUserState($context . '.data', null);
		
		$event = $eventModel->getTable('event');
		$event->load($data["event_id"]);
		
		$this->sendEmail($validData['id'], $validData, $event);
        
		$view = $this->getView('bookingconfirmation', 'html', 'site');
		$view->setModel($eventModel);
		$eventModel->setState('u3aevent.id', $event->id);
		$view->setModel($model, true);  // this is the booking model
		$model->setState('booking.id', $validData['id']);
		$view->display();
		
		return true;
	}
	
	public function delete($key = null, $urlVar = null)
    {
		// Function for handling the deletion of a booking
		// This can only be done via the booking edit layout, clicking on Delete Booking,
		// in which case we have the booking details in the POST data from the form
		// We check that the booking id matches the booking reference to avoid the case
		// where someone nasty just does a curl to this URL passing the id alone.
	
		// Check for request forgeries.
		$this->checkToken();
        
		$app = Factory::getApplication(); 
		$input = $app->input; 
		$model = $this->getModel('booking');
		$eventModel = $this->getModel('event');
		
		// Get the current URI to set in redirects. As we're handling a POST, 
		// this URI comes from the <form action="..."> attribute in the layout file above
		$currentUri = (string)Uri::getInstance();
       
		// get the data from the HTTP POST request
		$data  = $input->get('jform', array(), 'array');
        
		// Don't bother checking the user has Access to see the event
		// The check that the booking reference matches will suffice
		$existingBooking = $model->getItem($data['id']);
		if ($data['booking_ref_part'] != $existingBooking->booking_ref_part)
		{
			$this->setRedirect($currentUri, Text::_('COM_U3ABOOKING_INVALID_BOOKING_REFERENCE'), 'error');
			return false;
		}
		
		TicketReserverHelper::unreserveTickets($existingBooking->event_id, $existingBooking->num_tickets);
		// to match the delete() of AdminModel we need to pass by reference an array of the ids of records to be deleted
		$idArray = array($data['id']);
		$model->delete($idArray);
		
		// clear the session data in the form
		$context = "$this->option.edit.$this->context"; 
        $app->setUserState($context . '.data', null);
		
		// set up the redirect to the add booking page
		$addBookingPage = Route::_("index.php?option=com_u3abooking&view=booking&layout=add&id=0&eventid=" . $existingBooking->event_id, false, Route::TLS_DISABLE, true);
        $this->setRedirect($addBookingPage, Text::_(COM_U3ABOOKING_DELETE_SUCCESSFUL));
		return true;
	}
	
	public function sendEmail($id, $validData, $event)
	{
		// send an email confirmation of the booking
		// get the Mailer object, set up the email to be sent, and send it
		$mailer = Factory::getMailer();
		
		$mailer->isHtml(true);
		
		$mailer->addRecipient($validData['email']);
		
		$mailer->setSubject("Booking for " . $event->title);
		
		$bookingReference = $id . $validData['booking_ref_part'];
		
		$bookingAmendURL = Route::_("index.php?option=com_u3abooking&view=booking&layout=edit&id=$id&booking=$bookingReference", false, null, true);
		
		$plural = $validData['num_tickets'] == 1 ? "" : "s";
		$body = "You have booked " . $validData['num_tickets'] . " place" . $plural . " at the event " . $event->title . "<br><br>";
		$body .= $event->description . "<br><br>"; 
		$body .= "Your confirmed booking reference is " . $bookingReference . "<br>"; 
		$body .= 'To amend or delete your booking go to <a href="' . $bookingAmendURL .  '">Amend/Delete booking</a><br><br>';
		$body .= "Contact telephone number: " . $validData['telephone'] . "<br><br>";
		$body .= "Attendees: <br>" . $validData['attendees'] . "<br><br>";
		$body .= "Special requirements: <br>" . $validData['special_requirements'] . "<br><br>";
		$body .= "This email has been sent automatically from the NDA U3A booking system<br>";
		$body .= "Please do not reply to this email as any response will not be seen.";
		$mailer->setBody($body);
		try 
		{
			$mailer->send(); 
		}
		catch (\Exception $e)
		{
			Log::add('Send email exception: ' . $e->getMessage(), Log::ERROR, 'u3a-error');
		}
	}
	
	public function find($key = null, $urlVar = null)
    {
		// Check for request forgeries.
		$this->checkToken();
		
		// get the data from the HTTP POST request
		$app = Factory::getApplication(); 
		$input = $app->input; 
		$data = $input->get('jform', array(), 'array');
		
		$currentUri = (string)Uri::getInstance();
		
		// analyse the booking reference entered
		// it should be of the form:     id/booking_ref_part   but remove any whitespace (from copy/paste from email)
		$data["booking_ref_for_amendment"] = trim($data["booking_ref_for_amendment"]);
		$slash = strpos($data["booking_ref_for_amendment"], "/");
		if ($slash)
		{
			$id = substr($data["booking_ref_for_amendment"], 0, $slash);
			$booking_ref_part = substr($data["booking_ref_for_amendment"], $slash);

			$booking = $this->getModel('booking')->getTable('booking');
			$booking->load($id);
			
			if (isset($booking->booking_ref_part) && ($booking->booking_ref_part == $booking_ref_part))
			{
				$returnURL = base64_encode($currentUri);
				$this->setRedirect(Route::_(
					"index.php?option=com_u3abooking&view=booking&layout=edit&id=$id&return=$returnURL&booking=" . $data["booking_ref_for_amendment"], false));
				return true;
			}
			else
			{
				$this->setRedirect($currentUri, Text::_('COM_U3ABOOKING_INVALID_BOOKING_REFERENCE'), 'warning');
				return false;
			}
		}
		else
		{
			$this->setRedirect($currentUri, Text::_('COM_U3ABOOKING_INVALID_BOOKING_REFERENCE_FORMAT'), 'error');
			return false;
		}
	}
}