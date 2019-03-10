<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://github.com/flipboxfactory/patron-hubspot/blob/master/LICENSE
 * @link       https://github.com/flipboxfactory/patron-hubspot/
 */

namespace flipbox\patron\hubspot\settings;

use Craft;
use flipbox\patron\settings\BaseSettings;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
class HubSpotSettings extends BaseSettings
{
    /**
     * @var array
     */
    public $defaultScopes;

    /**
     * @return string
     * @throws \Twig_Error_Loader
     * @throws \yii\base\Exception
     */
    public function inputHtml(): string
    {
        return Craft::$app->getView()->renderTemplate(
            'patron-hubspot/providers/settings',
            [
                'settings' => $this
            ]
        );
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return array_merge(
            parent::rules(),
            [
                [
                    [
                        'defaultScopes'
                    ],
                    'required'
                ],
                [
                    [
                        'defaultScopes'
                    ],
                    'safe',
                    'on' => [
                        static::SCENARIO_DEFAULT
                    ]
                ]
            ]
        );
    }
}
