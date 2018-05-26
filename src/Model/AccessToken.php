<?php

/**
 * This file is part of richardhj/contao-epost-core.
 *
 * Copyright (c) 2015-2018 Richard Henkenjohann
 *
 * @package   richardhj/contao-epost-core
 * @author    Richard Henkenjohann <richardhenkenjohann@googlemail.com>
 * @copyright 2015-2018 Richard Henkenjohann
 * @license   https://github.com/richardhj/contao-epost-core/blob/master/LICENSE
 */

namespace Richardhj\ContaoEPostCoreBundle\Model;

use Contao\Model;
use Contao\System;
use League\OAuth2\Client\Token\AccessToken as OAuth2AccessToken;
use ParagonIE\Halite\Alerts\CannotPerformOperation;
use ParagonIE\Halite\KeyFactory;
use ParagonIE\Halite\Symmetric\Crypto as SymmetricCrypto;
use ParagonIE\Halite\Symmetric\EncryptionKey;


/**
 * @property string $data The access token data as json encoded string with token itself encrypted
 */
class AccessToken extends Model
{

    /**
     * @var string
     */
    protected static $strTable = 'tl_epost_access_token';

    /**
     * Persist a given AccessToken instance with encrypted token
     *
     * @param OAuth2AccessToken $objAccessToken
     *
     * @throws CannotPerformOperation
     * @throws \ParagonIE\Halite\Alerts\InvalidDigestLength
     * @throws \ParagonIE\Halite\Alerts\InvalidKey
     * @throws \ParagonIE\Halite\Alerts\InvalidMessage
     * @throws \ParagonIE\Halite\Alerts\InvalidType
     */
    public function saveAccessToken(OAuth2AccessToken $objAccessToken)
    {
        $arrData                 = $objAccessToken->jsonSerialize();
        $arrData['access_token'] = SymmetricCrypto::encrypt($arrData['access_token'], $this->getEncryptionKey());
        $this->data              = json_encode($arrData);

        $this->save();
    }


    /**
     * Load a AccessToken from database
     *
     * @return OAuth2AccessToken|null
     * @throws \InvalidArgumentException
     * @throws CannotPerformOperation
     * @throws \ParagonIE\Halite\Alerts\InvalidDigestLength
     * @throws \ParagonIE\Halite\Alerts\InvalidKey
     * @throws \ParagonIE\Halite\Alerts\InvalidMessage
     * @throws \ParagonIE\Halite\Alerts\InvalidSignature
     * @throws \ParagonIE\Halite\Alerts\InvalidType
     */
    public function createAccessToken(): ?OAuth2AccessToken
    {
        $arrData = json_decode($this->data, true);

        if (empty($arrData)) {
            return null;
        }

        $arrData['access_token'] = SymmetricCrypto::decrypt($arrData['access_token'], $this->getEncryptionKey());

        return new OAuth2AccessToken($arrData);
    }

    /**
     * @throws \ParagonIE\Halite\Alerts\InvalidKey
     * @throws CannotPerformOperation
     */
    private function getEncryptionKey(): EncryptionKey
    {
        $keyPath = System::getContainer()->getParameter('kernel.project_dir').'/var/epost/secret.key';
        try {
            $key = KeyFactory::loadEncryptionKey($keyPath);
        } catch (CannotPerformOperation $e) {
            $key = KeyFactory::generateEncryptionKey();
            KeyFactory::save($key, $keyPath);
        }

        return $key;
    }
}
