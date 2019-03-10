<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://github.com/flipboxfactory/patron-hubspot/blob/master/LICENSE
 * @link       https://github.com/flipboxfactory/patron-hubspot
 */

namespace flipbox\patron\hubspot\connections;

use Craft;
use craft\helpers\ArrayHelper;
use flipbox\craft\hubspot\connections\SavableIntegrationConnectionInterface;
use flipbox\craft\hubspot\HubSpot as HubSpotPlugin;
use flipbox\craft\integration\connections\AbstractSaveableConnection;
use Flipbox\OAuth2\Client\Provider\HubSpot;
use Flipbox\OAuth2\Client\Provider\HubSpotResourceOwner;
use flipbox\patron\records\Provider;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
class PatronConnection extends AbstractSaveableConnection implements SavableIntegrationConnectionInterface
{
    use AccessTokenAuthorizationTrait;

    /**
     * @var string
     */
    public $hubId;

    /**
     * @var string
     */
    public $appId;

    /**
     * @var HubSpotResourceOwner
     */
    private $resourceOwner;

    /**
     * @inheritdoc
     */
    public static function displayName(): string
    {
        return Craft::t('patron-hubspot', 'Patron OAuth Token');
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
                        'hubId',
                        'appId',
                        'provider'
                    ],
                    'required'
                ],
                [
                    [
                        'hubId',
                        'appId',
                        'provider'
                    ],
                    'safe',
                    'on' => [
                        static::SCENARIO_DEFAULT
                    ]
                ]
            ]
        );
    }

    /**
     * @inheritdoc
     * @throws \Throwable
     */
    public function afterSave(bool $isNew, array $changedAttributes = [])
    {
        // Delete existing lock
        if (null !== ($provider = ArrayHelper::getValue($changedAttributes, 'provider'))) {
            $condition = [
                (is_numeric($provider) ? 'id' : 'handle') => $provider,
                'environment' => null,
                'enabled' => null
            ];

            if (null !== ($provider = Provider::findOne($condition))) {
                $provider->removeLock(HubSpotPlugin::getInstance());
            }
        }

        $this->getRecord(false)->addLock(HubSpotPlugin::getInstance());

        parent::afterSave($isNew, $changedAttributes);
    }

    /**
     * @inheritdoc
     *
     * @throws \Twig_Error_Loader
     * @throws \yii\base\Exception
     */
    public function getSettingsHtml(): string
    {
        $providers = Provider::find()
            ->class(HubSpot::class)
            ->environment(null)
            ->enabled(null);

        return Craft::$app->view->renderTemplate(
            'patron-hubspot/connections/configuration',
            [
                'connection' => $this,
                'providers' => $providers->all()
            ]
        );
    }

    /**
     * @inheritdoc
     * @throws \yii\base\InvalidConfigException
     */
    public function getHubId(): string
    {
        if ($this->hubId === null) {
            if (null === ($hubId = $this->getFromAccessTokenValues('hubId'))) {
                $hubId = $this->getResourceOwner()->getHubId();
            }
            $this->hubId = $hubId ? (string)$hubId : null;
        }
        return $this->hubId;
    }

    /**
     * @inheritdoc
     * @throws \yii\base\InvalidConfigException
     */
    public function getAppId(): string
    {
        if ($this->appId === null) {
            if (null === ($appId = $this->getFromAccessTokenValues('appId'))) {
                $appId = $this->getResourceOwner()->getAppId();
            }
            $this->appId = $appId ? (string)$appId : null;
        }
        return $this->appId;
    }

    /**
     * @param string $attribute
     * @return string|null
     * @throws \yii\base\InvalidConfigException
     */
    protected function getFromAccessTokenValues(string $attribute)
    {
        $values = $this->getAccessToken()->getValues();
        $value = $values[$attribute] ?? null;
        return $value ? (string)$value : null;
    }

    /**
     * @return HubSpotResourceOwner
     * @throws \yii\base\InvalidConfigException
     */
    protected function getResourceOwner()
    {
        if ($this->resourceOwner === null) {
            $this->resourceOwner = $this->getProvider()->getResourceOwner(
                $this->getAccessToken()
            );
        }
        return $this->resourceOwner;
    }

    /**
     * @param bool $restricted
     * @return Provider
     */
    protected function getRecord(bool $restricted = true): Provider
    {
        // Get provider from settings
        if (null !== ($provider = $this->provider ?? null)) {
            $condition = [
                (is_numeric($provider) ? 'id' : 'handle') => $provider
            ];

            if ($restricted !== true) {
                $condition['environment'] = null;
                $condition['enabled'] = null;
            }

            $provider = Provider::findOne($condition);
        }

        if (!$provider instanceof Provider) {
            $provider = new Provider();
        }

        $provider->class = HubSpot::class;

        return $provider;
    }
}
