<?php
namespace Oauth\Controller;

use Oauth\Request;
use Oauth\Scope;
use Oauth\Response;
use Oauth\ClientAssertionType;
use Oauth\Storage;
use Oauth\ResponseType;
use Oauth\GrantType\GrantTypeInterface;

/**
 *  This controller is called when a token is being requested.
 *  it is called to handle all grant types the application supports.
 *  It also validates the client's credentials
 *
 *  ex:
 *  > $response = $grantController->handleGrantRequest(Request::createFromGlobals());
 *  > $response->send();
 *
 */
class Grant
{
    protected $response;
    protected $clientAssertionType;
    protected $accessToken;
    protected $grantTypes;
    protected $scopeUtil;

    public function __construct($clientAssertionType = null, ResponseType\AccessTokenInterface $accessToken, array $grantTypes = array(), Scope $scopeUtil = null)
    {
        if ($clientAssertionType instanceof Storage\ClientCredentialsInterface) {
            $clientAssertionType = new ClientAssertionType\HttpBasic($clientAssertionType);
        }
        if (!is_null($clientAssertionType) && !$clientAssertionType instanceof ClientAssertionType\ClientAssertionTypeInterface) {
            throw new LogicException('$clientAssertionType must be an instance of Storage\ClientCredentialsInterface, ClientAssertionTypeInterface, or null');
        }
        $this->clientAssertionType = $clientAssertionType;
        $this->accessToken = $accessToken;
        foreach ($grantTypes as $grantType) {
            $this->addGrantType($grantType);
        }

        if (is_null($scopeUtil)) {
            $scopeUtil = new Scope();
        }
        $this->scopeUtil = $scopeUtil;
    }

    public function handleGrantRequest(Request $request)
    {
        if ($token = $this->grantAccessToken($request)) {
            // @see http://tools.ietf.org/html/rfc6749#section-5.1
            // server MUST disable caching in headers when tokens are involved
            $this->response = new \Oauth\Response($token, 200, array('Cache-Control' => 'no-store', 'Pragma' => 'no-cache'));
        }
        return $this->response;
    }

    /**
     * Grant or deny a requested access token.
     * This would be called from the "/token" endpoint as defined in the spec.
     * You can call your endpoint whatever you want.
     *
     * @param $request - OAuth2_RequestInterface
     * Request object to grant access token
     * @param $grantType - mixed
     * GrantTypeInterface instance or one of the grant types configured in the constructor
     *
     * @throws InvalidArgumentException
     * @throws LogicException
     *
     * @see http://tools.ietf.org/html/rfc6749#section-4
     * @see http://tools.ietf.org/html/rfc6749#section-10.6
     * @see http://tools.ietf.org/html/rfc6749#section-4.1.3
     *
     * @ingroup oauth2_section_4
     */
    public function grantAccessToken(Request $request)
    {
        if (strtolower($request->server('REQUEST_METHOD')) != 'post') {
            $this->response = new Response\Error(400, 'invalid_request', 'The request method must be POST when requesting an access token', 'http://tools.ietf.org/html/draft-ietf-oauth-v2-31#section-3.2');
            return null;
        }

        // Determine grant type from request
        if (!$grantType = $request->request('grant_type')) {
            $this->response = new Response\Error(400, 'invalid_request', 'The grant type was not specified in the request');
            return null;
        }
        if (!isset($this->grantTypes[$grantType])) {
            /* TODO: If this is an OAuth2 supported grant type that we have chosen not to implement, throw a 501 Not Implemented instead */
            $this->response = new Response\Error(400, 'unsupported_grant_type', sprintf('Grant type "%s" not supported', $grantType));
            return null;
        }
        $grantType = $this->grantTypes[$grantType];

        // Hack to see if clientAssertionType is part of the grant type
        // this should change, but right now changing it will break BC
        $clientAssertionType = $grantType instanceof ClientAssertionType\ClientAssertionTypeInterface ? $grantType : $this->clientAssertionType;

        $clientData = $clientAssertionType->getClientDataFromRequest($request);

        if (!$clientData || !$clientAssertionType->validateClientData($clientData, $grantType->getQuerystringIdentifier())) {
            $this->response = $this->getObjectResponse($clientAssertionType, new Response\Error(400, 'invalid_request', 'Unable to verify client'));
            return null;
        }

        // validate the request for the token
        if (!$grantType->validateRequest($request)) {
            $this->response = $this->getObjectResponse($grantType, new Response\Error(400, 'invalid_request', sprintf('Invalid request for "%s" grant type', $grantType->getQuerystringIdentifier())));
            return null;
        }

        if (!$tokenData = $grantType->getTokenDataFromRequest($request)) {
            $this->response = $this->getObjectResponse($grantType, new Response\Error(400, 'invalid_grant', sprintf('Unable to retrieve token for "%s" grant type', $grantType->getQuerystringIdentifier())));
            return null;
        }

        if (!$grantType->validateTokenData($tokenData, $clientData)) {
            $this->response = $this->getObjectResponse($grantType, new Response\Error(400, 'invalid_grant', 'Token is no longer valid'));
            return null;
        }

        if (!isset($tokenData["scope"])) {
            $tokenData["scope"] = null;
        }

        $scope = $this->scopeUtil->getScopeFromRequest($request);
        // Check scope, if provided
        // @TODO: ScopeStorage
        if (null != $scope && (!is_array($tokenData) || !isset($tokenData["scope"]) || !$this->scopeUtil->checkScope($scope, $tokenData["scope"]))) {
            $this->response = new Response\Error(400, 'invalid_scope', 'An unsupported scope was requested.');
            return null;
        }

        $tokenData['user_id'] = isset($tokenData['user_id']) ? $tokenData['user_id'] : null;

        return $grantType->createAccessToken($this->accessToken, $clientData, $tokenData);
    }

    /**
     * addGrantType
     *
     * @param grantType - GrantTypeInterface
     * the grant type to add for the specified identifier
     * @param identifier - string
     * a string passed in as "grant_type" in the response that will call this grantType
     **/
    public function addGrantType(GrantTypeInterface $grantType, $identifier = null)
    {
        if (is_null($identifier) || is_numeric($identifier)) {
            $identifier = $grantType->getQuerystringIdentifier();
        }

        $this->grantTypes[$identifier] = $grantType;
    }

    public function getResponse()
    {
        return $this->response;
    }

    public function getObjectResponse($object, \Oauth\Response $defaultResponse = null)
    {
        if ($object instanceof Response\ProviderInterface && $response = $object->getResponse()) {
            return $response;
        }
        return $defaultResponse;
    }
}
