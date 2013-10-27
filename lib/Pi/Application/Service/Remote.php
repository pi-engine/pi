<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 * @package         Service
 */

namespace Pi\Application\Service;

use Pi;
use Zend\Http\Response;
use Zend\Http\Client\Adapter\AdapterInterface;
use Zend\Uri\Uri;

/**
 * Remote request handler service
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Remote extends AbstractService
{
    /** {@inheritDoc} */
    protected $fileIdentifier = 'remote';

    /**
     * HTTP client adapter
     *
     * @var AdapterInterface
     */
    protected $adapter;

    /**
     * Get adapter, instantiate it if not exist yet
     *
     * @param string $name
     * @param array $options
     *
     * @return AdapterInterface
     */
    public function adapter($name = '', array $options = array())
    {
        if ($name) {
            $adapter = $this->loadAdapter($name, $options);
        } else {
            if (!$this->adapter) {
                $name = $this->getOption('adapter');
                $options = array_merge($this->getOption($name), $options);
                $this->adapter = $this->loadAdapter($name, $options);
            }
            $adapter = $this->adapter;
        }

        return $adapter;
    }

    /**
     * Loads http client adapter
     *
     * @param string $name
     * @param array $options
     *
     * @return AdapterInterface
     */
    public function loadAdapter($name = '', array $options = array())
    {
        $class = sprintf('Zend\Http\Client\Adapter\%s', ucfirst($name));
        $adapter = new $class;
        if ($options) {
            $adapter->setOptions($options);
        }

        return $adapter;
    }

    /**
     * Connect to the remote server
     *
     * @param string $host
     * @param int    $port
     * @param bool   $secure
     *
     * @return void
     */
    public function connect($host, $port = 80, $secure = false)
    {
        return $this->adapter()->connect($host, $port, $secure);
    }

    /**
     * Send request to the remote server
     *
     * @param string        $method
     * @param Uri|string    $url
     * @param string        $httpVer
     * @param array         $headers
     * @param string        $body
     *
     * @return string|bool Request as text
     */
    public function write(
        $method,
        $url,
        $httpVer    = '1.1',
        $headers    = array(),
        $body       = ''
    ) {
        $method = strtoupper($method);
        if (!$url instanceof Uri) {
            $url = new Uri($url);
        }

        $headers = $this->canonizeHeaders($headers);

        try {
            $result = $this->adapter()->write(
                $method,
                $url,
                $httpVer,
                $headers,
                $body
            );
        } catch (\Exception $e) {
            $result = false;
            trigger_error('Remote access error: ' . $e->getMessage(), E_USER_WARNING);
        }

        return $result;
    }

    /**
     * Read response from server
     *
     * @return string|false
     */
    public function read()
    {
        try {
            $result = $this->adapter()->read();
        } catch (\Exception $e) {
            $result = false;
            trigger_error('Remote access error: ' . $e->getMessage(), E_USER_WARNING);
        }

        return $result;
    }

    /**
     * Close the connection to the server
     *
     * @return void
     */
    public function close()
    {
        return $this->adapter()->close();
    }

    /**
     * Parse fetched remote content to response
     *
     * @param string $content
     *
     * @return bool|array|string
     */
    protected function parseResponse($content = '')
    {
        try {
            $response = Response::fromString($content);
        } catch (\Exception $e) {
            $response = false;
            trigger_error('Response error: ' . $e->getMessage(), E_USER_WARNING);
        }
        if ($response && $response->isOk()) {
            $result         = $response->getBody();
            $contentType    = $response->getHeaders()->get('Content-Type');
            $isJson         = false;
            if ($contentType) {
                $value  = $contentType->getFieldValue();
                $isJson = false !== stripos($value, 'application/json');
            }
            if ($isJson) {
                $result = json_decode($result, true);
            }
        } else {
            $result = false;
        }

        return $result;
    }

    /**
     * Set http auth to headers
     *
     * @param array $headers
     *
     * @return array
     */
    protected function canonizeHeaders($headers = array())
    {
        if (!isset($headers['User-Agent'])) {
            $headers['User-Agent'] = 'Pi Engine cURL';
        }
        if (!array_key_exists('Authorization', $headers)) {
            $authHeader = $this->buildAuthorization($this->options);
            if ($authHeader) {
                $headers['Authorization'] = $authHeader;
            }
        }

        return $headers;
    }

    /**
     * Set options for authorization
     *
     * @param array|null $params
     *
     * @return $this
     */
    public function setAuthorization($params)
    {
        $params = $params ? : array();
        foreach (array('httpauth', 'username', 'password') as $key) {
            if (array_key_exists($key, $params)) {
                $this->options[$key] = $params[$key];
            }
        }

        return $this;
    }

    /**
     * Build authorization header
     *
     * @param array|null $params
     *
     * @return string
     */
    public function buildAuthorization($params)
    {
        $params = $params ? : array();
        $authorization = '';
        if (!empty($params['username']) && !empty($params['password'])) {
            $httpauth = !empty($params['httpauth'])
                ? ucfirst($params['httpauth']) : 'basic';
            $authorization = ucfirst($httpauth) . ' ' . base64_encode(
                $params['username'] . ':' . $params['password']
            );
        }

        return $authorization;
    }

    /**
     * Perform a GET request
     *
     * @param string            $url
     * @param array             $params
     * @param array             $headers
     * @param array|int|bool    $options
     *
     * @return mixed
     */
    public function get(
        $url,
        array $params = array(),
        array $headers = array(),
        $options = array()
    ) {
        /**@+
         * Check against cache
         */
        $cache = array();
        if (false !== $options) {
            $cacheOption = $this->getOption('cache');
            if (false !== $cacheOption && 'production' == Pi::environment()) {
                if (is_string($cacheOption)) {
                    $cache['storage'] = $cacheOption;
                } elseif (is_int($cacheOption)) {
                    $cache['ttl'] = $cacheOption;
                } elseif (is_array($cacheOption)) {
                    if (isset($cacheOption['cache'])) {
                        $cache = $cacheOption['cache'];
                    } else {
                        $cache = $cacheOption;
                    }
                }
                if (is_string($options)) {
                    $cache['storage'] = $options;
                } elseif (is_int($options)) {
                    $cache['ttl'] = $options;
                } elseif (is_array($options)) {
                    $cache = array_merge($cache, $options);
                }
            }
        }

        if ($cache) {
            $storage = null;
            $cacheOptions = array(
                'namespace' => 'remote',
            );
            if (!empty($cache['storage'])) {
                $storage = Pi::service('cache')->loadStorage($cache['storage']);
            }
            if (!empty($cache['ttl'])) {
                $cacheOptions['ttl'] = $cache['ttl'];
            }
            $cacheKey = md5($url . serialize($params) . serialize($headers));

            $cache = array();
            $cache['storage'] = $storage;
            $cache['key'] = $cacheKey;
            $cache['options'] = $cacheOptions;

            $data = Pi::service('cache')->getItem(
                $cache['key'],
                $cache['options'],
                $cache['storage']
            );

            if (null !== $data) {
                $result = json_decode($data, true);
                //d('Cache fetched.');
                return $result;
            }
        }
        /**@-*/

        $uri = new Uri($url);
        $host = $uri->getHost();
        $port = $uri->getPort();
        $this->adapter()->connect($host, $port);

        if ($params) {
            // FIXME: Convert sub arrays to string
            array_walk($params, function (&$param) {
                if (is_array($param)) {
                    $param = implode(',', $param);
                }
            });

            $uri->setQuery($params);
        }

        $headers = $this->canonizeHeaders($headers);
        $this->write('GET', $uri, '1.1', $headers);
        $response = $this->read();
        if (false !== $response) {
            $result = $this->parseResponse($response);
        } else {
            $result = false;
        }

        /**@+
         * Save to cache
         */
        if (false !== $result && $cache) {
            $data = json_encode($result);
            $status = Pi::service('cache')->setItem(
                $cache['key'],
                $data,
                $cache['options'],
                $cache['storage']
            );
            //d('Remote cache: ' . $status);
        }
        /**@-*/

        return $result;
    }

    /**
     * Perform a POST request
     *
     * @param string $url
     * @param array $params
     * @param array $headers
     * @param array $options
     *
     * @return mixed
     */
    public function post(
        $url,
        array $params = array(),
        array $headers = array(),
        array $options = array()
    ) {
        $uri = new Uri($url);
        $host = $uri->getHost();
        $port = $uri->getPort();
        $this->adapter()->connect($host, $port);

        if (!$params) {
            $body = '';
        } elseif (is_array($params)) {
            $body = http_build_query($params);
        } else {
            $body = $params;
        }
        $headers = $this->canonizeHeaders($headers);
        $this->write('POST', $url, '1.1', $headers, $body);
        $response = $this->read();
        if (false !== $response) {
            $result = $this->parseResponse($response);
        } else {
            $result = false;
        }

        return $result;
    }
}
