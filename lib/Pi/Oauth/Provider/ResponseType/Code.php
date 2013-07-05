<?php
namespace Pi\Oauth\Provider\ResponseType;

use Pi\Oauth\Provider\Service;

class Code extends AbstractResponseType
{
    public function process($params)
    {
        $code = Service::storage('authorization_code')->add(array(
            'client_id'     => $params['client_id'],
            'redirect_uri'  => $params['redirect_uri'],
            'scope'         => $params['scope'],
        ));

        // build the URL to redirect to
        $result = array('query' => array(
            'code'  => $code,
        ));

        if (isset($params['state'])) {
            $result['query']['state'] = $params['state'];
        }

        return $result;
    }
}