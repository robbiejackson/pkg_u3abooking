<?php

defined('_JEXEC') or die;

use Joomla\CMS\Dispatcher\ComponentDispatcherFactoryInterface;
use Joomla\CMS\Extension\ComponentInterface;
use Joomla\CMS\Extension\MVCComponent;
use Joomla\CMS\Extension\Service\Provider\ComponentDispatcherFactory;
use Joomla\CMS\Extension\Service\Provider\MVCFactory;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;
use Robbie\Component\U3ABooking\Administrator\Extension\U3ABookingComponent;
use Joomla\Database\DatabaseInterface;

return new class implements ServiceProviderInterface {
    
    public function register(Container $container): void {
        $container->registerServiceProvider(new MVCFactory('\\Robbie\\Component\\U3ABooking'));
        $container->registerServiceProvider(new ComponentDispatcherFactory('\\Robbie\\Component\\U3ABooking'));
        $container->set(
            ComponentInterface::class,
            function (Container $container) {
                $component = new U3ABookingComponent($container->get(ComponentDispatcherFactoryInterface::class));
                $component->setMVCFactory($container->get(MVCFactoryInterface::class));
                $component->setDatabase($container->get(DatabaseInterface::class));

                return $component;
            }
        );
    }
};