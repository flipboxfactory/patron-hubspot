<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://github.com/flipboxfactory/patron-hubspot/blob/master/LICENSE
 * @link       https://github.com/flipboxfactory/patron-hubspot/
 */

namespace flipbox\patron\hubspot;

use craft\base\Plugin;
use flipbox\craft\ember\modules\LoggerTrait;
use flipbox\craft\hubspot\cp\Cp as HubSpotCp;
use flipbox\craft\hubspot\events\RegisterConnectionsEvent;
use flipbox\patron\cp\Cp as PatronCp;
use flipbox\patron\events\RegisterProviderIcons;
use flipbox\patron\events\RegisterProviders;
use flipbox\patron\events\RegisterProviderSettings;
use flipbox\patron\hubspot\connections\PatronConnection;
use flipbox\patron\hubspot\settings\HubSpotSettings;
use Flipbox\OAuth2\Client\Provider\HubSpot;
use yii\base\Event;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
class Patron extends Plugin
{
    use LoggerTrait;

    /**
     * @return string
     */
    protected static function getLogFileName(): string
    {
        return 'patron';
    }

    /**
     * @inheritdocfind
     */
    public function init()
    {
        parent::init();

        // OAuth2 Provider
        Event::on(
            PatronCp::class,
            RegisterProviders::REGISTER_PROVIDERS,
            function (RegisterProviders $event) {
                $event->providers[] = HubSpot::class;
            }
        );

        // OAuth2 Provider Settings
        RegisterProviderSettings::on(
            HubSpot::class,
            RegisterProviderSettings::REGISTER_SETTINGS,
            function (RegisterProviderSettings $event) {
                $event->class = HubSpotSettings::class;
            }
        );

        // OAuth2 Provider Icon
        Event::on(
            PatronCp::class,
            RegisterProviderIcons::REGISTER_ICON,
            function (RegisterProviderIcons $event) {
                $event->icons[HubSpot::class] = '@vendor/flipboxfactory/patron-hubspot/icons/hubspot.svg';
            }
        );

        // Connection settings
        Event::on(
            HubSpotCp::class,
            RegisterConnectionsEvent::REGISTER_CONNECTIONS,
            function (RegisterConnectionsEvent $event) {
                $event->connections[] = PatronConnection::class;
            }
        );
    }
}
