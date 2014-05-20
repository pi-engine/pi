<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 * @package         Service
 */

namespace Pi\Application\Service;

use CurlFile;
use Pi;
use Zend\Http\Response;
use Zend\Http\Client\Adapter\AdapterInterface;
use Zend\Uri\Uri;

/**
 * Remote request handler service
 *
 * Remote access
 * ```
 *  $result = Pi::service('remote')->get(<uri>, <params[]>, <headers[]>, <options[]>);
 * ```
 *
 * Remote post
 * ```
 *  $result = Pi::service('remote')->post(<uri>, <params[]>, <headers[]>, <options[]>);
 * ```
 *
 * Remote upload
 * ```
 *  // Upload with POST method
 *  $file = </path/to/file>;
 *  $result = Pi::service('remote')->upload(<uri>, <file>, <params[]>, <headers[]>, <options[]>);
 *
 *  // Upload with POST, specified filename
 *  $file = array(
 *      'tmp_name'  => </path/to/file>,
 *      'type'      => <mimetype>,
 *  );
 *  $params = array(
 *      'filename'  => <desired-filename>,
 *      'param'     => <extra-params>,
 *  );
 *  $result = Pi::service('remote')->upload(<uri>, <file>, <params[]>, <headers[]>, <options[]>);
 *
 *  // Upload with PUT method
 *  // http://php.net/manual/en/features.file-upload.put-method.php
 *  $file = fopen('/path/to/file', 'r');
 *  $result = Pi::service('remote')->upload(<uri>, <file>, <params[]>, <headers[]>, <options[]>);
 * ```
 *
 * Remote download
 * ```
 *  $file = '/path/to/file';
 *  $result = Pi::service('remote')->download(<uri>, <file>, <params[]>, <headers[]>, <options[]>);
 *
 *  $file = fopen('/path/to/file', 'w');
 *  $result = Pi::service('remote')->download(<uri>, <file>, <params[]>, <headers[]>, <options[]>);
 * ```
 *
 * Authorization
 * ```
 *  Pi::service('remote')->setAuthorization(array('httpauth' => <>, 'username' => <>, 'password' => <>>))->write(...);
 * ```
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
    public function loadAdapter($name, array $options = array())
    {
        $class = sprintf('Pi\Http\Client\Adapter\%s', ucfirst($name));
        if (!class_exists($class)) {
            $class = sprintf('Zend\Http\Client\Adapter\%s', ucfirst($name));
        }
        $adapter = new $class;
        if ($options) {
            $adapter->setOptions($options);
        }

        return $adapter;
    }

    /**
     * Connect to the remote server
     *
     * @param string|Uri $host
     * @param int    $port
     * @param bool   $secure
     *
     * @return void
     */
    public function connect($host, $port = 80, $secure = false)
    {
        if ($host instanceof Uri) {
            $port   = $host->getPort();
            $secure = ('https' == $host->getScheme()) ? true : false;
            $host   = $host->getHost();
        }

        $this->adapter()->connect($host, $port, $secure);
    }

    /**
     * Send request to the remote server
     *
     * @param string        $method
     * @param Uri|string    $url
     * @param string        $httpVer
     * @param array         $headers
     * @param string        $body
     * @param array         $options
     *
     * @return string|bool Request as text
     */
    public function write(
        $method,
        $url,
        $httpVer    = '1.1',
        array $headers    = array(),
        $body       = '',
        array $options = array()
    ) {
        $method = strtoupper($method);
        if (!$url instanceof Uri) {
            $url = new Uri($url);
        }

        $headers = $this->canonizeHeaders($headers);
        if ($options) {
            $this->adapter()->setOptions($options);
        }
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
        $this->adapter()->close();
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
        if (!array_key_exists('Authorization', $headers)
            && ($auth = $this->getOption('authorization'))
        ) {
            $authHeader = $this->buildAuthorization($auth);
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
     * Canonize URL with params, set `appkey` if not specified yet
     *
     * @param string|Uri $uri
     * @param array $params
     *
     * @return Uri
     */
    protected function canonizeUrl($uri, array $params = array())
    {
        if (!$uri instanceof Uri) {
            $uri = new Uri($uri);
        }
        if (!isset($params['appkey'])) {
            $params['appkey'] = Pi::config('identifier');
        }
        $params = array_merge($uri->getQueryAsArray(), $params);
        $uri->setQuery($params);

        return $uri;
    }

    /**
     * Perform a GET request
     *
     * @param string|Uri    $url
     * @param array         $params
     * @param array         $headers
     * @param array         $options
     *
     * @return mixed
     */
    public function get(
        $url,
        array $params = array(),
        array $headers = array(),
        array $options = array()
    ) {
        if ($params) {
            // @FIXME: Convert sub arrays to string
            array_walk($params, function (&$param) {
                if (is_array($param)) {
                    $param = implode(',', $param);
                }
            });
        }
        $uri = $this->canonizeUrl($url, $params);

        /**@+
         * Check against cache
         */
        $cache = array();
        if (isset($options['cache'])) {
            $cache = $options['cache'];
            unset($options['cache']);
        }
        if (false !== $cache) {
            $cacheOption = $this->getOption('cache');
            if (false !== $cacheOption && 'production' == Pi::environment()) {
                if (is_string($cacheOption) && !isset($cache['storage'])) {
                    $cache['storage'] = $cacheOption;
                } elseif (is_int($cacheOption) && !isset($cache['ttl'])) {
                    $cache['ttl'] = $cacheOption;
                } elseif (is_array($cacheOption)) {
                    if (isset($cacheOption['cache'])) {
                        $cache = array_merge($cacheOption['cache'], $cache);
                    } else {
                        $cache = array_merge($cacheOption, $cache);
                    }
                }
            } else {
                $cache = false;
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
            $cacheKey = md5($uri->__toString() . serialize($headers));

            $cache = array(
                'storage'   => $storage,
                'key'       => $cacheKey,
                'options'   => $cacheOptions,
            );

            $data = Pi::service('cache')->getItem(
                $cache['key'],
                $cache['options'],
                $cache['storage']
            );

            if (null !== $data) {
                $result = json_decode($data, true);
                return $result;
            }
        }
        /**@-*/
        $this->connect($uri);

        $headers = $this->canonizeHeaders($headers);
        $this->write('GET', $uri, '1.1', $headers, '', $options);
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
        }
        /**@-*/

        return $result;
    }

    /**
     * Perform a POST request
     *
     * @param string|Uri    $url
     * @param array         $params
     * @param array         $headers
     * @param array         $options
     *
     * @return mixed
     */
    public function post(
        $url,
        array $params = array(),
        array $headers = array(),
        array $options = array()
    ) {
        $headers = $this->canonizeHeaders($headers);
        // Pass `CURLOPT_POSTFIELDS` as array with `Content-Type` header set to `multipart/form-data`
        if (isset($headers['Content-Type'])
            && 'multipart/form-data' == $headers['Content-Type']
        ) {
            $uri = $url;
            $body = $params;
        // Pass `CURLOPT_POSTFIELDS` as string
        } else {
            $uri = $this->canonizeUrl($url, $params);
            $body = $uri->getQuery();
        }
        $this->connect($uri);

        $this->write('POST', $uri, '1.1', $headers, $body, $options);
        $response = $this->read();
        if (false !== $response) {
            $result = $this->parseResponse($response);
        } else {
            $result = false;
        }

        return $result;
    }

    /**
     * Perform a upload request with cURL
     *
     * `$options`:
     *  - ftp:
     *      - username
     *      - password
     *      - timeout
     *
     * @param string|Uri $url
     * @param string|array|Resource $file
     * @param array $params
     * @param array $headers
     * @param array $options
     *
     * @return mixed
     */
    public function upload(
        $url,
        $file,
        array $params = array(),
        array $headers = array(),
        array $options = array()
    ) {
        @ignore_user_abort(true);
        @set_time_limit(0);

        $uri = $this->canonizeUrl($url, $params);

        $isFtp = false;
        $ftpOptions = array();
        if (isset($options['ftp'])) {
            $ftpOptions = $options['ftp'];
            unset($options['ftp']);
        }
        if ($ftpOptions) {
            $isFtp = true;
            $uri->setScheme('ftp');
        } elseif ('ftp' == $uri->getScheme()) {
            $isFtp = true;
        }

        // Upload via FTP
        if ($isFtp) {
            $result = $this->ftpUpload($uri, $file, $ftpOptions);

            return $result;
        }

        // Upload a file from absolute path via `POST`
        if (!is_resource($file)) {
            // @see http://www.php.net/curl_setopt
            // If value is an array, the `Content-Type` header will be set to `multipart/form-data`.
            // As of PHP 5.2.0, value must be an array if files are passed to this option with the @ prefix.
            $headers['Content-Type'] = 'multipart/form-data';

            if (is_array($file)) {
                $filename = $file['tmp_name'];
                $mimetype = isset($file['type']) ? $file['type'] : '';
                $postname = isset($file['name']) ? $file['name'] : '';
            } else {
                $filename = $file;
                $mimetype = '';
                $postname = '';
            }
            // As of PHP 5.5.0, the @ prefix is deprecated and files can be sent using `CURLFile`.
            if (class_exists('CurlFile')) {
                $curlFile = new CurlFile($filename, $mimetype, $postname);
            } else {
                $curlFile = '@' . $filename;
                if ($mimetype) {
                    $curlFile .= ';type=' . $mimetype;
                }
                if ($postname && !isset($params['filename'])) {
                    $params['filename'] = $postname;
                }
            }
            $params[] = $curlFile;
            $result = $this->post($uri, $params, $headers, $options);

            return $result;
        }

        // Upload a file resource via cURL `PUT`
        if (!isset($headers['Content-Length'])) {
            if (!isset($options['size'])) {
                $stat = fstat($file);
                $size = $stat['size'];
            } else {
                $size = $options['size'];
                unset($options['size']);
            }
        } else {
            $size = $headers['Content-Length'];
            unset($headers['Content-Length']);
        }
        $this->adapter()->setCurlOption(CURLOPT_INFILE, $file)
            ->setCurlOption(CURLOPT_INFILESIZE, $size);
        $this->connect($uri);

        $body = $uri->getQuery();
        $headers = $this->canonizeHeaders($headers);
        $this->write('PUT', $url, '1.1', $headers, $body, $options);
        $response = $this->read();
        $this->close();
        if (false !== $response) {
            $result = $this->parseResponse($response);
        } else {
            $result = false;
        }

        return $result;
    }

    /**
     * Perform a download request with cURL
     *
     * `$options`:
     *  - ftp:
     *      - username
     *      - password
     *      - timeout
     *
     * @param string|Uri $url
     * @param string|Resource $file
     * @param array $params
     * @param array $headers
     * @param array $options
     *
     * @return mixed
     */
    public function download(
        $url,
        $file,
        array $params = array(),
        array $headers = array(),
        array $options = array()
    ) {
        @ignore_user_abort(true);
        @set_time_limit(0);

        $uri = $this->canonizeUrl($url, $params);

        $isFtp = false;
        $ftpOptions = array();
        if (isset($options['ftp'])) {
            $ftpOptions = $options['ftp'];
            unset($options['ftp']);
        }
        if ($ftpOptions) {
            $isFtp = true;
            $uri->setScheme('ftp');
        } elseif ('ftp' == $uri->getScheme()) {
            $isFtp = true;
        }

        // Download via FTP
        if ($isFtp) {
            $result = $this->ftpDownload($uri, $file, $ftpOptions);

            return $result;
        }

        // Upload a file from absolute path via `POST`
        if ($file && !is_resource($file)) {
            $file = @fopen($file, 'w');
        }
        if (!$file) {
            return false;
        }
        $this->adapter()->setOutputStream($file);
        // Disable cache for download
        $options['cache'] = false;
        $result = $this->get($uri, $params, $headers, $options);

        return $result;
    }

    /**
     * Perform a upload request via FTP
     *
     * `$options`:
     *  - username
     *  - password
     *  - timeout
     *
     * @param string|Uri      $url
     * @param string|Resource $file
     * @param array           $options
     *
     * @return bool
     */
    public function ftpUpload(
        $url,
        $file,
        array $options = array()
    ) {
        if (is_resource($file)) {
            $resource = $file;
        } else {
            $resource = @fopen($file, 'r');
        }
        if (!$resource) {
            return false;
        }
        $stat = fstat($resource);
        $size = $stat['size'];

        $uri = $this->canonizeUrl($url);
        $curl = $this->ftpCurl($uri, $options);

        curl_setopt($curl, CURLOPT_UPLOAD, 1);
        curl_setopt($curl, CURLOPT_INFILE, $resource);
        curl_setopt($curl, CURLOPT_INFILESIZE, $size);
        curl_exec($curl);
        $error = curl_errno($curl);
        curl_close($curl);

        if (!is_resource($file)) {
            fclose($resource);
        }
        $result = $error ? false : true;

        return $result;
    }

    /**
     * Perform a download request via FTP
     *
     * `$options`:
     *  - username
     *  - password
     *  - timeout
     *
     * @param string|Uri      $url
     * @param string|Resource $file
     * @param array           $options
     *
     * @return bool
     */
    public function ftpDownload(
        $url,
        $file,
        array $options = array()
    ) {
        if (is_resource($file)) {
            $resource = $file;
        } else {
            $resource = @fopen($file, 'w');
        }
        if (!$resource) {
            return false;
        }

        $uri = $this->canonizeUrl($url);
        $curl = $this->ftpCurl($uri, $options);

        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_FILE, $resource);
        curl_exec($curl);
        $error = curl_errno($curl);
        curl_close($curl);

        if (!is_resource($file)) {
            fclose($resource);
        }
        $result = $error ? false : true;

        return $result;
    }

    /**
     * Build cURL for FTP
     *
     * @param Uri   $uri
     * @param array $options
     *
     * @return resource
     * @throws \InitializationException
     */
    protected function ftpCurl(Uri $uri, array $options)
    {
        if (!extension_loaded('curl')) {
            throw new \InitializationException('cURL extension is not installed.');
        }

        $uri->setScheme('ftp');
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $uri->__toString());
        if (!empty($options['username']) && !empty($options['password'])) {
            $userpwd = $options['username'] . ':' . $options['password'];
            curl_setopt($curl, CURLOPT_USERPWD, $userpwd);
        }
        if (isset($options['timeout'])) {
            curl_setopt($curl, CURLOPT_TIMEOUT, (int) $options['timeout']);
        }

        return $curl;
    }
}
