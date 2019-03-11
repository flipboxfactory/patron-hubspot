<?php

/**
 * @copyright  Copyright (c) Flipbox Digital Limited
 * @license    https://github.com/flipboxfactory/patron-hubspot/blob/master/LICENSE
 * @link       https://github.com/flipboxfactory/patron-hubspot/
 */

namespace flipbox\patron\hubspot\connections;

use flipbox\patron\Patron;
use League\OAuth2\Client\Token\AccessToken;
use League\OAuth2\Client\Token\AccessTokenInterface;

/**
 * @author Flipbox Factory <hello@flipboxfactory.com>
 * @since 1.0.0
 */
trait AccessTokenTrait
{
    use ProviderTrait;

    /**
     * @var AccessTokenInterface|AccessToken|null
     */
    private $accessToken;

    /**
     * @param AccessTokenInterface|AccessToken $accessToken
     */
    public function setAccessToken(AccessTokenInterface $accessToken)
    {
        $this->accessToken = $accessToken;
    }

    /**
     * @return AccessTokenInterface|AccessToken
     * @throws \yii\base\InvalidConfigException
     */
    public function getAccessToken(): AccessTokenInterface
    {
        if (!$this->accessToken instanceof AccessTokenInterface) {
            $token = Patron::getInstance()->getTokens([
                'provider' => $this->getProvider()
            ])->one();

            $this->accessToken = $token;
        }

        return $this->accessToken;
    }
}
