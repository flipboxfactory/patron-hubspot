<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://github.com/flipboxfactory/patron-hubspot/blob/master/LICENSE
 * @link       https://github.com/flipboxfactory/patron-hubspot/
 */

namespace flipbox\patron\hubspot\connections;

use Flipbox\OAuth2\Client\Provider\HubSpot;
use flipbox\patron\queries\ProviderQuery;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
trait ProviderTrait
{
    /**
     * @var mixed
     */
    public $provider;

    /**
     * @param $provider
     * @return $this
     */
    public function setProvider($provider)
    {
        $this->provider = $provider;
        return $this;
    }

    /**
     * @return HubSpot
     * @throws \yii\base\InvalidConfigException
     */
    public function getProvider(): HubSpot
    {
        if ($this->provider instanceof HubSpot) {
            return $this->provider;
        }

        // Get provider from settings
        if (null !== ($provider = $this->provider ?? null)) {
            $condition = [
                (is_numeric($provider) ? 'id' : 'handle') => $provider
            ];
            $provider = (new ProviderQuery($condition))->one();
        }

        if (!$provider instanceof HubSpot) {
            $provider = new HubSpot();
        }

        return $provider;
    }
}