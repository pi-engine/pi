<?php
namespace Pi\Oauth\Provider\Storage;

/**
 * Create refresh_code if required
 *
 * @see http://tools.ietf.org/html/rfc6749#section-4.1.4 Optional for authorization_code grant_type
 * @see http://tools.ietf.org/html/rfc6749#section-4.2.2 Must not for implicit grant_type
 * @see http://tools.ietf.org/html/rfc6749#section-4.3.3 Optional for password grant_type
 * @see http://tools.ietf.org/html/rfc6749#section-4.4.3 Must not for client_credentials grant_type
 */
class RefreshToken extends AbstractStorage implements CodeInterface
{
    public function add($params)
    {
        if (!isset($params['token'])) {
            $params['token'] = $this->generateCode($this->config['length']);
        }

        parent::add($params);
        return $params['token'];
    }
}