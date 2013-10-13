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

    /** @var  bool Is remote server connected */
    //protected $isConnected;

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
        //$this->isConnected = false;
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
     * @return string Request as text
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
        return $this->adapter()->write(
            $method,
            $url,
            $httpVer,
            $headers,
            $body
        );
    }

    /**
     * Read response from server
     *
     * @return string
     */
    public function read()
    {
        return $this->adapter()->read();
    }

    /**
     * Close the connection to the server
     *
     * @return void
     */
    public function close()
    {
        //$this->isConnected = false;
        return $this->adapter()->close();
    }

    /**
     * Perform a GET request
     *
     * @param string $url
     * @param array $params
     *
     * @return mixed
     */
    public function get($url, array $params = array())
    {
        $uri = new Uri($url);
        $host = $uri->getHost();
        $port = $uri->getPort();
        $this->adapter()->connect($host, $port);

        if ($params) {
            $uri->setQuery($params);
        }
        $this->write('GET', $uri);
        $result = $this->read();
        $result = json_decode($result, true);

        return $result;
    }

    /**
     * Perform a POST request
     *
     * @param string $url
     * @param array $params
     *
     * @return mixed
     */
    public function post($url, array $params = array())
    {
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
        $this->write('POST', $url, '1.1', array(), $body);
        $result = $this->read();
        $result = json_decode($result, true);

        return $result;
    }
}
