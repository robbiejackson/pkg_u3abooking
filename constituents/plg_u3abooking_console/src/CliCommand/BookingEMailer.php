<?php
namespace Robbie\Plugin\Console\U3ABooking\CliCommand;

defined('_JEXEC') or die;

use Joomla\CMS\Date\Date;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class BookingEMailer extends \Joomla\Console\Command\AbstractCommand
{

    // name of this command
    protected static $defaultName = 'u3abooking:mailbookings';

    public function doExecute(InputInterface $input, OutputInterface $output): int
    {
        $now = new Date('now');
        $nowSQL = $now->toSQL();

        $db = Factory::getDbo();
        $query = $db->getQuery(true);
        $query->select('id, title, alias, organiser_email')
            ->from('#__u3a_event')
            ->where('event_start > "' . $nowSQL . '"')
            ->where('published = 1');
        
        $db->setQuery($query);
        
        $rows = $db->loadObjectList();
        
        $body   =   'List of events\n';
        
        foreach($rows as $row)
        {
            $body .= $row->title . ", " . $row->organiser_email . "\n";
            //echo "\n" . $row->title . "\n" . $row->organiser_email . "\n";
            $this->sendBookings($row->id, $row->alias, $row->title, $row->organiser_email);
        }

        return 0;
    }
    
    public function sendBookings($eventid, $eventAlias, $eventTitle, $organiser)
    {
        $csvfilename = JPATH_ROOT . '/tmp/bookings-' . $eventAlias . '.csv';
        $f = fopen($csvfilename, 'w'); 
        
        // write the header row
        $row = array('Date', 'Booking ref', 'Telephone', 'Email address', 'Tickets', 'Attendees', 'Special requirements');
        fputcsv($f, $row);
        
        $db = Factory::getDbo();
        $query = $db->getQuery(true);

        $query->select('created, CONCAT(id,booking_ref_part) AS booking_ref, telephone, email, num_tickets, attendees, special_requirements')
                ->from($db->quoteName('#__u3a_booking'))
                ->where($db->quoteName('event_id') . ' = ' . $eventid)
                ->order($db->quoteName('created'));
        $db->setQuery($query);
        
        $rows = $db->loadRowList();
        
        //echo count($rows) . " rows found\n";
        
        foreach ($rows as $row)
        {
            fputcsv($f, $row);
        }
        fclose($f);
        
        $config = Factory::getConfig();
        $mailer = Factory::getMailer();
        $sender = array( 
            $config->get('mailfrom'),
            $config->get('fromname') 
        );

        $mailer->SMTPDebug = true;

        $mailer->addRecipient($organiser);
        $now = date('H:i j F Y');
        $mailer->setSubject("Bookings as at $now for event $eventTitle");
        $mailer->addAttachment($csvfilename);
        $mailer->setBody("Load the attached file into a spreadsheet");

        $send = $mailer->Send();
        
        if ( $send !== true ) {
            echo 'Error sending email: ';
        } else {
            //echo 'Email sent';
        }
    }
    
    // Configure the command description and help text
    protected function configure(): void
    {
        $this->setDescription("Email event organisers the bookings against their events");
        $this->setHelp("Run this command to send a csv file of bookings to the event organisers");
    }

}