<?php
/**
 * Layout for displaying the booking confirmation
 */
 
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Uri\Uri;

$home = Uri::root();  // home page of the site - for the Continue button

$plural = $this->booking->num_tickets == 1 ? "" : "s";
$body = "You have booked " . $this->booking->num_tickets . " place" . $plural . " at the event " . $this->event->title . "<br><br>";
if($this->event)
{
	$body .= $this->event->description . "<br><br>"; 
}
$body .= "Your confirmed booking reference is " . $this->booking->id . $this->booking->booking_ref_part . "<br>"; 
$body .= "You will need to enter this booking reference to make any changes to your booking<br><br>";
$body .= "An email has also be sent to your email address with confirmation of this booking<br><br>";
$body .= "Contact telephone number: " . $this->booking->telephone . "<br><br>";
$body .= "Attendees: <br>" . $this->booking->attendees . "<br><br>";
$body .= "Special requirements: <br>" . $this->booking->special_requirements . "<br><br>";
?>
<?php if($this->event): ?>
<p>
	<?php echo $body; ?>
</p>
<?php endif; ?>
<div>
	<a href="<?php echo $home; ?>" class="btn btn-primary" role="button">Continue</a>
</div>
