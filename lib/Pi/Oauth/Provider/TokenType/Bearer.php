<?php
namespace Pi\Oauth\Provider\TokenType;

use Pi\Oauth\Provider\Service;
use Pi\Oauth\Provider\Http\Request;
//use Pi\Oauth\Provider\Result;

class Bearer extends AbstractTokenType
{
    protected $identifier = 'bearer';

    //protected $isHeader;

    /**
     * This is a convenience function that can be used to get the token, which can then
     * be passed to getAccessTokenData(). The constraints specified by the draft are
     * attempted to be adheared to in this method.
     *
     * As per the Bearer spec (draft 8, section 2) - there are three ways for a client
     * to specify the bearer token, in order of preference: Authorization Header,
     * POST and GET.
     *
     * NB: Resource servers MUST accept tokens via the Authorization scheme
     * (http://tools.ietf.org/html/rfc6750#section-2).
     *
     * @todo Should we enforce TLS/SSL in this function?
     *
     * @see http://tools.ietf.org/html/rfc6750#section-2.1
     * @see http://tools.ietf.org/html/rfc6750#section-2.2
     * @see http://tools.ietf.org/html/rfc6750#section-2.3
     *
     * Old Android version bug (at least with version 2.2)
     * @see http://code.google.com/p/android/issues/detail?id=6684
     */
    public function getAccessToken(Request $request)
    {
        $tokenParam = null;
        $headers = $request->getHeader('Authorization');

        // Check that exactly one method was used
        $methodsUsed = !empty($headers) + (null !== $request->getQuery('access_token')) + (null !== $request->getPost('access_token'));
        if ($methodsUsed > 1) {
            $this->setError('invalid_request', 'Only one method may be used to authenticate at a time (Auth header, GET or POST)');
            return $tokenParam;
        }
        if ($methodsUsed == 0) {
            $this->setError('invalid_request', 'The access token was not found');
            return null;
        }

        // HEADER: Get the access token from the header
        if (!empty($headers)) {
            if (!preg_match('/Bearer\s(\S+)/', $headers, $matches)) {
                $this->setError('invalid_request', 'Malformed authorization header');
                return null;
            }
            $tokenParam = $matches[1];
            //$this->isHeader = true;

        } elseif ($request->getPost('access_token')) {
            // POST: Get the token from POST data
            if (!$request->isPost()) {
                $this->setError('invalid_request', 'When putting the token in the body, the method must be POST');
                return null;
            }

            if ($request->getServer('CONTENT_TYPE') !== null && $request->getServer('CONTENT_TYPE') != 'application/x-www-form-urlencoded') {
                // IETF specifies content-type. NB: Not all webservers populate this _SERVER variable
                $this->setError('invalid_request', 'The content type for POST requests must be "application/x-www-form-urlencoded"');
                return null;
            }

            $tokenParam = $request->getPost('access_token');
        } else {
            // GET method
            $tokenParam = $request->getQuery('access_token');
        }

        /**
         * @see http://tools.ietf.org/html/rfc6750#section-2.1
         *  b64token = 1*( ALPHA / DIGIT / "-" / "." / "_" / "~" / "+" / "/" ) *"="
         */
        $b64TokenRegExp = '(?:[[:alpha:][:digit:]-._~+/]+=*)';
        $result = preg_match('|^' . $b64TokenRegExp . '$|', $tokenParam);
        if (!$result) {
            $tokenParam = null;
            $this->setError('invalid_token', 'The access token is malformed');
        }

        return $tokenParam;
    }

    public function setError($error, $errorDescription = null, $errorUri = null, $statusCode = 400)
    {
        $this->result = Service::error('resource_bearer_error', $error, $errorDescription, $errorUri, $statusCode);
        return $this;
    }
}