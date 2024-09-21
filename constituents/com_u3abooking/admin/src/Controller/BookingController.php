<?php
namespace Robbie\Component\U3ABooking\Administrator\Controller;

/**
 * Controller for handling booking.xxx tasks
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\MVC\Controller\FormController;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Factory;
use Joomla\CMS\Table\Table;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use Robbie\Component\U3ABooking\Administrator\Helper\TicketReserverHelper;

class BookingController extends FormController
{
	public function add()
	{
		$this->setRedirect(Route::_('index.php?option=com_u3abooking&view=bookings', false),
			Text::_('JLIB_APPLICATION_ERROR_CREATE_RECORD_NOT_PERMITTED'), 'error');
		return false;
	}
	
	public function save($key = null, $urlVar = null)
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
		
		// for redirecting back to the booking form
		$bookingsURL = Route::_('index.php?option=com_u3abooking&view=bookings', false);
		
		// set up the normal redirect depending on whether it's Save (ie task=apply) or Save & Close (ie task=save)
		$task = $this->getTask();
		$normalRedirectURL = ($task == 'apply') ? $currentUri : $bookingsURL;
       
	    // get the data from the HTTP POST request
		$data  = $input->get('jform', array(), 'array');
	   
	    // Access check.
		if (!Factory::getApplication()->getIdentity()->authorise('core.edit', 'com_u3abooking'))
		{
			$this->setMessage(Text::_('JLIB_APPLICATION_ERROR_SAVE_NOT_PERMITTED'), 'error');

			$this->setRedirect($normalRedirectURL);

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
		$existingBooking = $model->getItem($validData['id']);
		$forceReserve = isset($validData['override_capacity']) && ($validData['override_capacity'] == 1);
		if ($validData["num_tickets"] > $existingBooking->num_tickets)
		{
			// try to allocate the extra tickets
			$ticketsToReserve = $validData['num_tickets'] - $existingBooking->num_tickets;
			$tickets_reserved = TicketReserverHelper::reserveTickets($validData['event_id'], $ticketsToReserve, $forceReserve);
			
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
					$app->enqueueMessage("Sorry, only 1 extra place was available", 'notice');
				}
				else
				{
					$app->enqueueMessage("Sorry, only " . $tickets_reserved . " extra places were available", 'notice');
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
			$this->setMessage(Text::sprintf('JLIB_APPLICATION_ERROR_SAVE_FAILED', $model->getError()), 'error');
			$this->setRedirect($currentUri);
			
			Log::add($this->getError(), Log::ERROR, 'admin-save-booking');

			return false;
		}
        
		// data has been saved ok, so clear the data in the form
		$app->setUserState($context . '.data', null);
		
		if (isset($validData['send_email']) && ($validData['send_email'] == 1))
		{
			$event = $eventModel->getTable();
			$event->load($validData["event_id"]);
			$this->sendEmail($validData['id'], $validData, $event);
		}
		
		$this->setRedirect($normalRedirectURL, Text::_('JLIB_APPLICATION_SAVE_SUCCESS'), 'message');
		
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
		
		$bookingAmendURL = Route::link("site", "index.php?option=com_u3abooking&view=booking&layout=edit&id=$id&booking=$bookingReference", false, Route::TLS_DISABLE, true);
		
		$plural = $validData['num_tickets'] == 1 ? "" : "s";
		$body = "Your booking has been changed by an administrator.<br><br>";
		$body .= "You have booked " . $validData['num_tickets'] . " place" . $plural . " at the event " . $event->title . "<br><br>";
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

}