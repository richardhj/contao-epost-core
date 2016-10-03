<?php
/**
 * E-POSTBUSINESS API integration for Contao Open Source CMS
 * Copyright (c) 2015-2016 Richard Henkenjohann
 * @package E-POST
 * @author  Richard Henkenjohann <richard-epost@henkenjohann.me>
 */

namespace EPost\Model;


use Contao\Model;
use League\OAuth2\Client\Token\AccessToken as OAuth2AccessToken;


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
     */
    public function saveAccessToken(OAuth2AccessToken $objAccessToken)
    {
        $arrData = $objAccessToken->jsonSerialize();
        $arrData['access_token'] = \Encryption::encrypt($arrData['access_token']);
        $this->data = json_encode($arrData);

        $this->save();
    }


    /**
     * Load a AccessToken from database
     *
     * @return OAuth2AccessToken|null
     */
    public function createAccessToken()
    {
        $arrData = json_decode($this->data, true);

        if (empty($arrData)) {
            return null;
        }

        $arrData['access_token'] = \Encryption::decrypt($arrData['access_token']);

        return new OAuth2AccessToken($arrData);
    }
}
