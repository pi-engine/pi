<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Pi\Setup;

/**
 * Host handler
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Host
{
    protected $wizard;
    protected $persist = '';

    /**
     * Detector file for detecting a path or URL
     * @var array
     */
    protected $detector = array(
        'file'      => 'detector.gif',
        'mimetype'  => 'image/gif',
    );

    /**
     * List of section paths
     * @var array
     */
    protected $paths = array(
        'www'       => '',
        'lib'       => '',
        'var'       => '',
        'usr'       => '',
        'static'    => '',
        'upload'    => '',
    );

    /**
     * List of path validation: 1 - valid; -1 - invalid; 0 - not check.
     * @var array
     */
    protected $validPath = array(
        'www'       => -1,
        'var'       => -1,
        'lib'       => -1,
        'usr'       => -1,
        'static'    => 0,
        'upload'    => -1,
    );

    /**
     * List of URI validation: 1 - valid; -1 - invalid; 0 - not check.
     * @var array
     */
    protected $validUrl = array(
        'static'    => -1,
        'upload'    => -1,
    );

    /**
     * Permission error message list of paths
     * @var array
     */
    protected $permErrors = array();

    /**
     * Constructor
     *
     * @param Wizard $wizard
     * @param string $persist Persistent data variable name
     */
    public function __construct(Wizard $wizard = null, $persist = '')
    {
        $this->wizard = $wizard;
        $this->persist = $persist;
    }

    /**
     * Initialize path information
     *
     * @param bool $initPath Whether initialize path URI based on config data
     * @return void
     */
    public function init($initPath = false)
    {
        $this->setRequest();
        $writablePaths = $this->wizard->getConfig('writable');
        foreach (array_keys($writablePaths) as $key) {
            $this->permErrors[$key] = false;
        }
        $paths = $this->wizard->getPersist($this->persist);
        // Load from persistent
        if ($paths) {
            foreach ($this->paths as $key => &$path) {
                $path = array(
                    'path'  => $paths[$key]['path'],
                    'url'   => isset($paths[$key]['url'])
                        ? $paths[$key]['url'] : '',
                );
            }
        // Initialize
        } else {
            // Initialize www path and URI
            $request = $this->wizard->getRequest();
            $baseUrl = $request->getScheme() . '://'
                     . $request->getHttpHost() . $request->getBaseUrl();
            $this->paths['www'] = array(
                'path'  => dirname($this->wizard->getRoot()),
                'url'   => dirname($baseUrl)
            );

            foreach ($this->wizard->getConfig('paths') as $key => $inits) {
                // Initialize path
                foreach ((array) $inits['path'] as $init) {
                    if ($init{0} === '%') {
                        list($idx, $loc) = explode('/', $init, 2);
                        $idx = substr($idx, 1);
                        if (isset($this->paths[$idx]['path'])) {
                            $init = $this->paths[$idx]['path'] . '/' . $loc;
                        }
                    } else {
                        $init = $this->paths['www']['path'] . '/' . $init;
                    }
                    $path = preg_replace('/\w+\/\.\.\//', '', $init);
                    if (is_dir($path . '/')) break;
                }
                $this->paths[$key]['path'] = $path;

                if (empty($initPath) || !isset($inits['url'])
                    || false === $inits['url']
                ) {
                    continue;
                }
                // Initialize URI
                foreach ((array) $inits['url'] as $init) {
                    if ($init{0} === '%') {
                        list($idx, $loc) = explode('/', $init, 2);
                        $idx = substr($idx, 1);
                        if (isset($this->paths[$idx]['url'])) {
                            $init = $this->paths[$idx]['url'] . '/' . $loc;
                        }
                    }
                    $this->paths[$key]['url'] = $init;
                    //if (0 <= $this->checkUrl($key)) break;
                }
            }

            $this->wizard->setPersist($this->persist, $this->paths);
        }
    }

    /**
     * Setup request
     *
     * @return void
     */
    protected function setRequest()
    {
        $request = $this->wizard->getRequest();
        $paths = $this->wizard->getPersist($this->persist);
        foreach ($this->paths as $key => &$path) {
            $reqKey = 'path_' . $key;
            if (null !== $request->getPost($reqKey)) {
                $req = str_replace(
                    '\\',
                    '/',
                    trim($request->getPost($reqKey))
                );
                $paths[$key]['path'] = rtrim($req, '/');
            }
            $reqKey = 'url_' . $key;
            if (null !== $request->getPost($reqKey)) {
                $req = str_replace(
                    '\\',
                    '/',
                    trim($request->getPost($reqKey))
                );
                $paths[$key]['url'] = rtrim($req, '/');
            }
        }
        $this->wizard->setPersist($this->persist, $paths);
    }

    /**
     * Validate all paths and URIs
     *
     * @return bool
     */
    public function validate()
    {
        $ret = true;
        foreach (array_keys($this->paths) as $key) {
            if ($this->checkPath($key) >= 0) {
                $this->checkPermissions($key);
            } else {
                $ret = false;
            }
            $result = $this->checkUrl($key);
            if ($result < 0) {
                $ret = false;
            }
        }
        foreach ($this->permErrors as $key => $errs) {
            if (empty($errs)) continue;
            foreach ($errs as $path => $status) {
                if (empty($status)) {
                    $ret = false;
                    break;
                }
            }
        }

        return $ret;
    }

    /**
     * Checks if a section path exists
     *
     * @param string $path The key of path to be checked
     * @return int  Potential values: 1 - valid; -1 - invalid; 0 - not check
     */
    public function checkPath($path = '')
    {
        if (isset($this->paths[$path]['path'])) {
            // Path is found and readable
            if (is_dir($this->paths[$path]['path'])
                && is_readable($this->paths[$path]['path'])
            ) {
                $this->validPath[$path] = 1;
            } elseif (!empty($this->paths[$path]['path'])) {
                $this->validPath[$path] = -1;
            }
            $ret = $this->validPath[$path];
        } else {
            $ret = -1;
        }

        return $ret;
    }

    /**
     * Checks write permissions of a section path
     *
     * @param string $path The key of path to be checked
     * @return void
     */
    private function checkPermissions($path)
    {
        if (!isset($this->paths[$path]['path'])) {
            return;
        }
        if (!isset($this->permErrors[$path])) {
            return;
        }
        $writablePaths = $this->wizard->getConfig('writable');
        $errors = array();
        $this->setWritePermission(
            $this->paths[$path]['path'],
            $writablePaths[$path],
            $errors
        );
        if (!empty($errors) && in_array(0, array_values($errors))) {
            $this->permErrors[$path] = $errors;
        }

        return;
    }

    /**
     * Checks if URI of a section is accessible
     *
     * @param string $key The key of URL to be checked
     * @return int  Potenial values: 1 - valid; -1 - invalid; 0 - not check
     */
    public function checkUrl($key = '')
    {
        $ret = 0;
        if (isset($this->paths[$key]['url']) && isset($this->validUrl[$key])) {
            $ret = $this->validUrl[$key];
            if (!empty($this->paths[$key]['url'])) {
                $method = sprintf('validateUrl%s', ucfirst($key));

                // Use dedicated method if available
                if (is_callable(array($this, $method))) {
                    $res = $this->{$method}($this->paths[$key]['url']);
                } else {
                    $res = $this->validateUrl($this->paths[$key]['url']);
                }

                if ($res === null) {
                    $this->validUrl[$key] = 0;
                } else {
                    $this->validUrl[$key] = empty($res) ? -1 : 1;
                }
                $ret = $this->validUrl[$key];
            }
        }

        return $ret;
    }

    /**
     * Checks if a section path and sub paths are writable and attemps to
     * set right permissions if not writable
     *
     * @param string    $path The key of path to be checked
     * @return array    Error messages
     */
    public function checkSub($path)
    {
        if (!isset($this->paths[$path]['path'])) {
            return array();
        }
        if (!isset($this->permErrors[$path])) {
            return array();
        }
        $writablePaths = $this->wizard->getConfig('writable');
        if (!isset($writablePaths[$path])) {
            return array();
        }
        $errors = array();
        $this->setWritePermission(
            $this->paths[$path]['path'],
            $writablePaths[$path],
            $errors
        );
        foreach ($errors as $key => $status) {
            if ($status != 0) {
                unset($errors[$key]);
            }
        }
        $this->permErrors[$path] = $errors;

        return $this->permErrors[$path];
    }

    /**
     * Check if an image URI is valid
     *
     * @param string $url
     * @param bool $appendDetector Wether to append detector file to the URL
     * @return bool|null
     */
    private function validateImageUrl($url, $appendDetector = false)
    {
        $mimeType = $this->detector['mimetype'];
        if ($appendDetector) {
            $url .= '/' . $this->detector['file'];
        }

        $ret = $this->validateUrl($url, $mimeType);

        return $ret;
    }

    /**
     * Check if asset URL if valid
     *
     * @param string $url
     * @return bool|null
     */
    private function validateUrlAsset($url)
    {
        return $this->validateImageUrl($url, true);
    }

    /**
     * Check if static URL is valid
     *
     * @param string $url
     * @return bool|null
     */
    private function validateUrlStatic($url)
    {
        return $this->validateImageUrl($url, true);
    }

    /**
     * Check if upload URL if valid
     *
     * @param string $url
     * @return bool|null
     */
    private function validateUrlUpload($url)
    {
        return $this->validateImageUrl($url, true);
    }

    /**
     * Formulate a URI to a complete URI
     *
     * URI to be formulated:
     *
     *  - '://': already a complete URI, return directly;
     *  - with leading slash '/': prepend protocol and host;
     *  - w/o leading slash '/': prepend Pi Engine 'www' URI
     *
     * @param string $url
     * @return string
     */
    private function formulateUrl($url)
    {
        if (strpos($url, '://') !== false) {
            return $url;
        }

        if ($url{0} != '/') {
            $url = (isset($this->paths['www']['url'])
                 ? $this->paths['www']['url'] : '') . '/' . $url;
        } else {
            $proto  = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on')
                ? 'https' : 'http';
            $host   = $_SERVER['HTTP_HOST'];
            $url    = $proto . '://' .  $host . $url;
        }

        return $url;
    }

    /**
     * Check if a URL is valid
     *
     * @param string $url
     * @param string $mimeType
     * @return bool|null
     */
    private function validateUrl($url = '', $mimeType = '')
    {
        if ($this->wizard->getConfig('skip_url_validate')) {
            return null;
        }

        $url = $this->formulateUrl($url);

        $ret = null;
        // Try cURL first if it is available.
        if (function_exists('curl_exec')) {
            $ch = curl_init();
            $options = array(
                CURLOPT_URL             => $url,
                CURLOPT_NOBODY          => true,
                CURLOPT_TIMEOUT         => 1,
                CURLOPT_USERAGENT       => 'Pi Engine',
                CURLOPT_FOLLOWLOCATION  => false,
                CURLOPT_MAXREDIRS       => 0,
            );
            curl_setopt_array($ch, $options);
            curl_exec($ch);
            if (!curl_errno($ch)) {
                if (!empty($mimeType)) {
                    $contentType = curl_getinfo($ch, CURLINFO_CONTENT_TYPE);
                    // Content-Type might not be detected correctly by CURL
                    // Thus return null instead of false if not matched
                    $ret = (strpos($contentType, $mimeType) !== false)
                         ? true : null;
                } else {
                    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                    $ret = ($httpCode == 200) ? true : false;
                }
            }
            curl_close($ch);
        }

        // Try get_headers if allow_url_fopen is enabled
        if (null === $ret && ini_get('allow_url_fopen')) {
            // Set options to disable redirects,
            // otherwise get_headers will return multiple responses
            $opts = array(
                'http' => array(
                    'max_redirects' => 0,
                    'ignore_errors' => 1
                )
            );
            stream_context_get_default($opts);
            $result = @get_headers($url, 1);
            // Check if HTTP code is 200
            $ret = preg_match('#HTTP/[^\s]+[\s]200([\s]?)#i', $result[0],
                              $matches);
            // Check content type match if specified
            if ($ret && !empty($contentType)) {
                $ret = strpos($result['Content-Type'], $contentType) !== false
                     ? true : false;
            }
        }

        return $ret;
    }

    /**
     * Sets write permission to a path
     *
     * @param string        $parent The key of parent path
     * @param string|array  $path   Key of path or array of paths/files
     * @param array         $error   Error messages
     * @return void
     */
    private function setWritePermission($parent, $path, &$error)
    {
        if (is_array($path)) {
            foreach (array_keys($path) as $item) {
                if (is_string($item)) {
                    $error[$parent . '/' . $item] =
                        $this->makeWritable($parent . '/' . $item);
                    if (empty($path[$item])) continue;
                    foreach ($path[$item] as $child) {
                        $this->setWritePermission($parent . '/' . $item,
                                                  $child, $error);
                    }
                } else {
                    $error[$parent . '/' . $path[$item]] =
                        $this->makeWritable($parent . '/' . $path[$item]);
                }
            }
        } else {
            $error[$parent . '/' . $path] =
                $this->makeWritable($parent . '/' . $path);
        }

        return;
    }

    /**
     * Write-enable the specified folder
     *
     * @param string $path
     * @param bool $recurse
     * @param bool $create
     * @return int  1 for successful; 0 for failed
     */
    private function makeWritable($path, $recurse = true, $create = true)
    {
        clearstatcache();
        $modeFolder = intval('0777', 8);
        $modeFile = intval('0666', 8);
        $isNew = false;
        if (!file_exists($path)) {
            if (!$create) {
                return 0;
            } else {
                (false === strpos(basename($path), '.'))
                    ? mkdir($path, $modeFolder) : touch($path);
                $isNew = true;
            }
        }
        if (!is_writable($path)) {
            @chmod($path, is_file($path) ? $modeFile : $modeFolder);
        }
        $status = is_writable($path) ? 1 : 0;
        if (!$isNew && $status && $recurse && is_dir($path)) {
            $iterator = new \DirectoryIterator($path);
            foreach ($iterator as $fileinfo) {
                if ($fileinfo->isDot()) {
                    continue;
                }
                $status = $status * $this->makeWritable(
                    $fileinfo->getPathname(),
                    $recurse,
                    $create
                );
                if (!$status) {
                    break;
                }
            }
        }

        return $status;
    }

    /**
     * Get path
     *
     * @param string $name
     * @return string|null
     */
    public function getPath($name)
    {
        list($type, $key) = explode('_', $name, 2);
        return isset($this->paths[$key][$type])
            ? $this->paths[$key][$type] : null;
    }

    /**
     * Set path
     *
     * @param string $name
     * @param string $value
     * @return void
     */
    public function setPath($name, $value)
    {
        list($type, $key) = explode('_', $name, 2);
        $this->paths[$key][$type] = $value;
        $this->wizard->setPersist($this->persist, $this->paths);
    }
}
