<?php
namespace Pi\Oauth\Provider\Result;

/**
 * @see http://tools.ietf.org/html/rfc6750#section-3.1
 * - invalid_request: 400
 * - invalid_token: 401
 * - insufficient_scope: 403
 */
class ResourceBearerError extends Error
{
    protected $errors = array(
        'invalid_request'           =>  'The request is missing a required parameter, includes an unsupported parameter or parameter value, repeats the same parameter, uses more than one method for including an access token, or is otherwise malformed.',
        'invalid_token'             =>  'The access token provided is expired, revoked, malformed, or invalid for other reasons.',
        'insufficient_scope'        =>  'The request requires higher privileges than provided by the access token.',
    );
    protected $errorType = 'token-bearer';

    public function setStatusCode($statusCode)
    {
        if ($this->error == 'invalid_client') {
            $statusCode = 401;
        } elseif ($this->error == 'insufficient_scope') {
            $statusCode = 403;
        }
        parent::setStatusCode($statusCode);

        return $this;
    }

    public function setAuthenticateHeader($realm, $scope = null)
    {

    }
}