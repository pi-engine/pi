<?php
namespace Pi\Oauth\Provider;

use Pi;
use Pi\Oauth\Provider\Server\AbstratServer;
use Pi\Oauth\Provider\Storage\AbstractStorage;
use Pi\Oauth\Provider\Storage\ModelInterface;
//use Pi\Oauth\Provider\Storage\Model\LoaderInterface;
use Pi\Oauth\Provider\GrantType\AbstractGrantType;
use Pi\Oauth\Provider\ResponseType\AbstractResponseType;
use Pi\Oauth\Provider\TokenType\AbstratTokenType;
use Pi\Oauth\Provider\Result;
use Pi\Oauth\Provider\Http\Request;
use Pi\Oauth\Provider\Http\Response;
use Pi\Oauth\Provider\Utility\Scope;

class Service
{
    /**
     * Registry for objects
     *  + server
     *      - authorization
     *      - grant
     *      - resource
     *  + storage
     *      - client
     *      - authorization_code
     *      - access_token
     *      - refresh_token
     *  + grant_type
     *      - authorization_code
     *      - client_credentials
     *      - password
     *      - refresh_token
     *      - urn:ietf:params:oauth:grant-type:jwt-bearer (jwt-bearer)
     *  + response_type
     *      - code
     *      - token
     *  + token_type
     *      - bearer
     *      - mac
     *  + result
     *      - authorization_error
     *      - error
     *      - grant_error
     *      - grant_implicit_error
     *      - grant_response
     *      - redirect
     *      - resource_bearer_error
     *      - reource_error
     *      - response
     *  - request
     *  - response
     *  - scope
     *  - resource_owner
     *
     * @var array
     */
    protected static $registry;

    /**
     * Configs
     *
     *  + config
     *      + server
     *          - authorization
     *              * response_types[]
     *          - grant
     *              * grant_types[]
     *          - resource
     *              * www_realm
     *      + storage
     *          - model_set
     *          - access_token
     *              * expires_in
     *              * length
     *              * token_type
     *          - refresh_token
     *              * expires_in
     *              * length
     *          - authorization_code
     *              * expires_in
     *              * length
     *
     * @var array
     */
    protected static $config;

    public static function canonizeName($name)
    {
        return str_replace(' ', '', ucwords(str_replace('_', ' ', $name)));
    }

    public static function boot($config)
    {
        if (is_string($config)) {
            $config = (array) include $config;
        }
        if (!isset($config['model_set'])) {
            $config['model_set'] = function ($identifier)
            {
                return Pi::model($identifier, 'oauth');
            };
        }
        static::$config = $config;
    }

    public static function config($section, $identifier)
    {
        if (isset(static::$config[$section]) && isset(static::$config[$section][$identifier])) {
            return static::$config[$section][$identifier];
        }
        return array();
    }

    public static function server($identifier)
    {
        $key = __FUNCTION__ . '-' . $identifier;
        if (!isset(static::$registry[$key])) {
            $class = __NAMESPACE__ . '\\Server\\' . static::canonizeName($identifier);
            static::$registry[$key] = new $class(static::config('servier', $identifier));
        }

        return static::$registry[$key];
    }

    public static function storage($identifier, ModelInterface $model = null)
    {
        $key = __FUNCTION__ . '-' . $identifier;
        if (!isset(static::$registry[$key])) {
            $configs = static::config('storage', $identifier);
            if (!$model) {
                $modelSet = isset($configs['model_set']) ? $configs['model_set'] : 'database';
                if ($modelSet instanceof \Closure) {
                    $model = $modelSet($identifier);
                } else {
                    $classLoader = __NAMESPACE__ . '\\Storage\\Model\\' . ucfirst($modelSet) . '\\Loader';
                    $model = $classLoader::load($identifier);
                }
            }
            $config = isset($configs[$identifier]) ? $configs[$identifier] : array();
            $config['model'] = $model;
            $classStorage = __NAMESPACE__ . '\\Storage\\' . static::canonizeName($identifier);
            static::$registry[$key] = new $classStorage($config);
        }

        return static::$registry[$key];
    }

    public static function grantType($identifier)
    {
        $key = __FUNCTION__ . '-' . $identifier;
        if (!isset(static::$registry[$key])) {
            $config = static::config('server', 'grant');
            if (isset($config['grant_types']) && isset($config['response_types'][$identifier])) {
                $className = $config['grant_types'][$identifier];
            } else {
                $className = static::canonizeName($identifier);
            }
            $class = __NAMESPACE__ . '\\GrantType\\' . $className;
            static::$registry[$key] = new $class;
        }

        return static::$registry[$key];
    }

    public static function responseType($identifier)
    {
        $key = __FUNCTION__ . '-' . $identifier;
        if (!isset(static::$registry[$key])) {
            $class = __NAMESPACE__ . '\\ResponseType\\' . static::canonizeName($identifier);
            static::$registry[$key] = new $class(static::config('response_type', $identifier));
        }

        return static::$registry[$key];
    }

    public static function tokenType($identifier)
    {
        $key = __FUNCTION__ . '-' . $identifier;
        if (!isset(static::$registry[$key])) {
            $class = __NAMESPACE__ . '\\TokenType\\' . static::canonizeName($identifier);
            static::$registry[$key] = new $class(static::config('token_type', $identifier));
        }

        return static::$registry[$key];
    }

    public static function result($identifier = 'response', $params = null)
    {
        $key = __FUNCTION__ . '-' . $identifier;
        if (!isset(static::$registry[$key])) {
            $class = __NAMESPACE__ . '\\Result\\' . static::canonizeName($identifier);
            static::$registry[$key] = new $class($params);
        }

        return static::$registry[$key];
    }

    public static function error($identifier, $error, $errorDescription = null, $errorUri = null, $statusCode = 400)
    {
        $key = __FUNCTION__ . '-' . $identifier;
        if (!isset(static::$registry[$key])) {
            $class = __NAMESPACE__ . '\\Result\\' . static::canonizeName($identifier);
            static::$registry[$key] = new $class($error, $errorDescription, $errorUri, $statusCode);
        }

        return static::$registry[$key];
    }

    public static function request()
    {
        $key = __FUNCTION__;
        if (!isset(static::$registry[$key])) {
            $class = __NAMESPACE__ . '\\Http\\Request';
            static::$registry[$key] = new $class;
        }

        return static::$registry[$key];
    }

    public static function response()
    {
        $key = __FUNCTION__;
        if (!isset(static::$registry[$key])) {
            $class = __NAMESPACE__ . '\\Http\\Response';
            static::$registry[$key] = new $class;
        }

        return static::$registry[$key];
    }

    public static function scope($scopeData = null)
    {
        $class = __NAMESPACE__ . '\\Utility\\Scope';
        $scope = new $class($scopeData);
        return $scope;
    }

    public static function resourceOwner($resourceOwner = null)
    {
        $key = 'resource_owner';
        if (null === $resourceOwner) {
            return isset(static::$registry[$key]) ? static::$registry[$key] : null;
        }
        static::$registry[$key] = $resourceOwner;
    }
}