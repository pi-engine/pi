<?php
namespace Pi\Oauth\Provider\ResponseType;

use Pi\Oauth\Provider\Service;

class Token extends AbstractResponseType
{
    public function process($params)
    {
        $tokenData = Service::storage('access_token')->add(array(
            'client_id'     => $params['client_id'],
            'scope'         => $params['scope'],
        ));

        // build the URL to redirect to
        $result = array(
            'fragment'  => array(
                'access_token'  => $tokenData['token'],
                'expires_in'    => $tokenData['expires_in'],
                'token_type'    => $tokenData['token_type'],
                'scope'         => $params['scope'],
            ),
        );

        /**
         * Include client state if provided
         */
        if (isset($params['state'])) {
            $result['fragment']['state'] = $params['state'];
        }

        return $result;
    }
}