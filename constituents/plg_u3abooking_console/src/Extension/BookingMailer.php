<?php

/**
 * @package     Joomla.Plugin
 * @subpackage  Behaviour.taggable
 *
 * @copyright   (C) 2016 Open Source Matters, Inc. <https://www.joomla.org>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

namespace Robbie\Plugin\Console\U3ABooking\Extension;

use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\Event\SubscriberInterface;
use Joomla\Application\ApplicationEvents;
use Joomla\Event\Event;
use Joomla\CMS\Factory;
use Joomla\DI\Container;
use Robbie\Plugin\Console\U3ABooking\CliCommand\BookingEMailer;

\defined('_JEXEC') or die;

final class BookingMailer extends CMSPlugin implements SubscriberInterface
{
    public static function getSubscribedEvents(): array
    {
        return [
            ApplicationEvents::BEFORE_EXECUTE => 'registerCLICommand',
        ];
    }
    
    public function registerCLICommand(Event $event)
    {
        $app = Factory::getApplication();
        $app->addCommand(new BookingEMailer());
    }

}