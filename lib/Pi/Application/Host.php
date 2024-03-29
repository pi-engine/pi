<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 */

namespace Pi\Application;

/**
 * Host handler
 *
 * Single host
 *
 * - Specify host file in `www/boot.php`
 *
 *  ```
 *      define('PI_PATH_HOST', '/path/to/pi/var/config/host.php');
 *  ```
 *
 * - Define host specification details in the specified host file
 *
 *  ```
 *      return array(
 *          'uri'   => array(
 *              ...
 *          ),
 *          'path'  => array(
 *              ...
 *          ),
 *      );
 *  ```
 *
 * Multiple hosts
 *
 * - Specify hosts file in `www/boot.php`
 *
 *  ```
 *      define('PI_PATH_HOST', '/path/to/pi/var/config/hosts-config.php');
 *  ```
 *
 * - Define hosts specification details in the specified hosts file,
 *  {@see var/config/hosts.php} for sample
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Host
{
    /**
     * Base URL, segment after baseLocation in installed URL
     * which is: (<scheme>:://<host-name>[:<port>])<baseUrl> with leading slash
     *
     * @var string
     */
    protected $baseUrl = '';

    /**
     * Base location: `<scheme>:://<host-name>[:<port>]`
     *
     * @var string
     */
    protected $baseLocation = '';

    /**
     * Specified URIs
     *
     * @var array
     */
    protected $uri
        = [
            'www'    => '',
            'asset'  => '',
            'upload' => '',
            'static' => '',
        ];

    /**
     * Specified paths
     *
     * @var array
     */
    protected $path
        = [
            // paths specified in local hosts file
            'www'    => '',
            'asset'  => '',
            'upload' => '',
            'static' => '',
            'usr'    => '',
            'module' => '',
            'theme'  => '',

            // paths defined in boot.php or in application host
            'lib'    => '',
            'var'    => '',

            // path dependent on var
            'config' => '',

            // paths dependent on var or specified in host file
            'cache'  => '',
            'log'    => '',
        ];

    /**
     * Paths dependent on other paths
     *
     * @var array
     */
    protected $directory
        = [
            'public' => [
                'parent' => 'www',
                'folder' => 'public',
            ],
            'asset'  => [
                'parent' => 'www',
                'folder' => 'asset',
            ],
            'upload' => [
                'parent' => 'www',
                'folder' => 'upload',
            ],
            'static' => [
                'parent' => 'www',
                'folder' => 'static',
            ],
            'module' => [
                'parent' => 'usr',
                'folder' => 'module',
            ],
            'theme'  => [
                'parent' => 'usr',
                'folder' => 'theme',
            ],
            'custom' => [
                'parent' => 'usr',
                'folder' => 'custom',
            ],
            'config' => [
                'parent' => 'var',
                'folder' => 'config',
            ],
            'log'    => [
                'parent' => 'var',
                'folder' => 'log',
            ],
        ];

    /**
     * Constructor
     *
     * @param string|array $config Host file path or array of path settings
     *
     * @return \Pi\Application\Host
     */
    public function __construct($config = [])
    {
        $this->setHost($config);
    }

    /**
     * Build base location
     *
     * @return string
     */
    protected function getBaseLocation()
    {
        // Build current request URI
        $scheme = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] == 'on')
            ? 'https' : 'http';
        $host   = $_SERVER['HTTP_HOST'];
        if (!$host) {
            $port = $_SERVER['SERVER_PORT'];
            $name = $_SERVER['SERVER_NAME'];
            if (($scheme == 'http' && $port == 80)
                || ($scheme == 'https' && $port == 443)
            ) {
                $host = $name;
            } else {
                $host = $name . ':' . $port;
            }
        }
        $baseLocation = $scheme . '://' . $host;

        return $baseLocation;
    }

    /**
     * Lookup host configuration file path in central host configuration
     *
     * @param array  $config
     * @param string $hostIdentifier
     *
     * @return array
     */
    protected function lookup($config, $hostIdentifier = '')
    {
        // Valid host data, return directly
        if (isset($config['path']) || isset($config['uri'])) {
            return $config;
        }
        // Invalid hosts data, return empty data
        if (!isset($config['hosts']) || !isset($config['alias'])) {
            trigger_error('Invalid hosts config.', E_USER_ERROR);
            return [];
        }

        // Build current request URI
        $uri        = isset($_SERVER['REQUEST_URI'])
            ? $_SERVER['REQUEST_URI'] : $_SERVER['SCRIPT_NAME'];
        $requestUri = rtrim(
            $this->getBaseLocation()
                . ($uri ? '/' . trim($uri, '/') : ''),
            '/'
        ) . '/';

        // Lookup identifier against alias list
        $lookup = function ($conf) use ($requestUri) {
            foreach ($conf as $uri => $identifier) {
                $uri = rtrim($uri, '/') . '/';
                if (0 === strpos($requestUri, $uri)) {
                    return $identifier;
                }
            }

            return false;
        };

        // Find identifier
        if (!$hostIdentifier) {
            $hostIdentifier = $lookup($config['alias']) ?: 'default';
        }
        // Get host data
        $hostData = $config['hosts'][$hostIdentifier];
        // Read from file
        if (is_string($hostData)) {
            $hostData = include $hostData;
        }

        return $hostData;
    }

    /**
     * Set host data based on passed config or data loaded from config file
     *
     * @param string|array $config Host file path or array of path settings
     *
     * @return self
     */
    public function setHost($config)
    {
        $hostConfig     = [];
        $hostFile       = '';
        $hostIdentifier = '';

        // Host file path is specified
        if (is_string($config)) {
            $hostFile = $config;
            $config   = [];
        } elseif (isset($config['file'])) {
            $hostFile = $config['file'];
            unset($config['file']);
        }

        // Get host identifier
        if (isset($config['identifier'])) {
            $hostIdentifier = $config['identifier'];
            unset($config['identifier']);
        }
        // Get custom host config
        if (isset($config['host'])) {
            $hostConfig = $config['host'];
            unset($config['host']);
        }
        // Load host data from file
        if ($hostFile) {
            $config = include $hostFile;
        }

        // Find host config data
        $configs = $this->lookup($config, $hostIdentifier);
        // Merge with custom host config
        if (isset($hostConfig['path'])) {
            $hostConfig['path'] = array_merge(
                $configs['path'],
                $hostConfig['path']
            );
        } else {
            $hostConfig['path'] = $configs['path'];
        }
        if (isset($hostConfig['uri'])) {
            $hostConfig['uri'] = array_merge(
                $configs['uri'],
                $hostConfig['uri']
            );
        } else {
            $hostConfig['uri'] = $configs['uri'];
        }
        if (isset($hostConfig['directory'])) {
            $hostConfig['directory'] = array_merge(
                $configs['directory'],
                $hostConfig['directory']
            );
        } elseif (!empty($configs['directory'])) {
            $hostConfig['directory'] = $configs['directory'];
        }

        // Canonize www URI
        if (empty($hostConfig['uri']['www'])) {
            $hostConfig['uri']['www'] = $this->getBaseLocation();
        }

        // Load from config file
        $this->path = $hostConfig['path'];
        $this->uri  = $hostConfig['uri'];
        if (!empty($hostConfig['directory'])) {
            $this->directory = $hostConfig['directory'];
        }

        // Set baseLocation
        $pos = strpos($hostConfig['uri']['www'], '/', 9);
        if ($pos === false) {
            $this->baseLocation = $hostConfig['uri']['www'];
            $this->baseUrl      = '';
        } else {
            $this->baseLocation = substr($hostConfig['uri']['www'], 0, $pos);
            $this->baseUrl      = substr($hostConfig['uri']['www'], $pos);
        }

        // Set dependent paths
        foreach (['config', 'cache', 'log'] as $path) {
            if (empty($this->path[$path])) {
                $this->path[$path] = $this->path['var'] . '/' . $path;
            }
        }

        return $this;
    }

    /**
     * Get a protected variable
     *
     * @param string $var
     *
     * @return mixed
     */
    public function get($var)
    {
        if (isset($this->$var)) {
            return $this->$var;
        }

        return null;
    }

    /**
     * Get value for a protected variable
     *
     * @param string $var
     * @param mixed  $value
     *
     * @return self
     */
    public function set($var, $value = null)
    {
        $this->$var = $value;

        return $this;
    }

    /**
     * Convert Pi Engine path to corresponding physical one
     *
     * For path value to be examined:
     *
     *  - With `:` or leading slash `/` - absolute path, do not convert;
     *  - Otherwise, first part as section, map to `www` if no section matched
     *
     * @param string $url
     *
     * @return string
     * @see Pi::path()
     */
    public function path($url)
    {
        $uri     = null;
        $section = null;
        $path    = null;
        // Path of predefined section, w/o sub path
        if (!empty($this->path[$url])) {
            [$section, $path] = [$url, ''];
        // Relative path with predefined directory
        } elseif (!empty($this->directory[$url])) {
            $directory = $this->directory[$url];
            $section   = $directory['parent'];
            $path      = $directory['folder'];
        // Relative path
        } elseif (false === strpos($url, ':') && $url[0] !== '/') {
            // No '/' included, map to www path
            if (false === strpos($url, '/')) {
                [$section, $path] = ['www', $url];
            // Split at the first '/'
            } else {
                [$section, $path] = explode('/', $url, 2);
                // If $root is not a section, match to www
                if (!isset($this->path[$section])) {
                    [$section, $path] = ['www', $url];
                }
            }
            if (!empty($this->directory[$section])) {
                $directory = $this->directory[$section];
                $section   = $directory['parent'];
                $path      = $directory['folder'] . '/' . $path;
            }
        } else {
            $uri = $url;
        }

        if (null === $uri) {
            // Convert section path
            $sectionUri = $this->path[$section];
            if (false !== strpos($sectionUri, ':') || $sectionUri[0] === '/') {
                $uri = $sectionUri;
            } else {
                // Append www path to sectionUri if it is relative
                $uri = $this->path['www']
                    . ($sectionUri ? '/' . $sectionUri : '');
            }
            // Assemble full path
            $uri .= $path ? '/' . $path : '';
        }

        return $uri;
    }

    /**
     * Convert a Pi Engine path to an URL
     *
     * For URL to be examined:
     *
     *  - With URI scheme `://` - absolute URI, do not convert, otherwise
     *  - With leading `//` - globally absolute URI, do not convert, otherwise
     *  - With leading `/` - in-Pi absolute URI, prepend with baseLocation, otherwise
     *  - First part as section, map to `www` if no section matched, otherwise
     *  - If section URI is relative, `www` URI will be appended.
     *
     * @param string $url
     * @param bool   $absolute
     *  Convert to full URI; Default as relative URI with no hostname
     *
     * @return string
     * @see Pi::url()
     */
    public function url($url = '', $absolute = false)
    {
        $uri     = null;
        $section = null;
        $path    = null;
        // URI of predefined section, w/o sub path
        if (!empty($this->uri[$url])) {
            [$section, $path] = [$url, ''];
        // Relative URI with predefined directory
        } elseif (!empty($this->directory[$url])
            && !empty($this->uri[$this->directory[$url]['parent']])
        ) {
            $directory = $this->directory[$url];
            $section   = $directory['parent'];
            $path      = $directory['folder'];
        // Absolute URI with leading `//`
        } elseif (0 === strpos($url, '//')) {
            $uri = $url;
        // In-Pi absolute URI with leading `/`
        } elseif (0 === strpos($url, '/')) {
            $uri = $this->baseLocation . $url;
        // Absolute URI with scheme
        } elseif (false !== strpos($url, '://')) {
            $uri = $url;
        // Relative URI
        } else {
            // No '/' included, map to www path
            if (false === strpos($url, '/')) {
                [$section, $path] = ['www', $url];
            // Split at the first '/'
            } else {
                [$section, $path] = explode('/', $url, 2);
                // If $root is not a section, match to www
                if (!isset($this->uri[$section])) {
                    [$section, $path] = ['www', $url];
                }
            }
            if (!empty($this->directory[$section])) {
                $directory = $this->directory[$section];
                $section   = $directory['parent'];
                $path      = $directory['folder'] . '/' . $path;
            }
        }

        if (null === $uri) {
            // Convert section URI
            $sectionUri = $this->uri[$section];
            if (false !== strpos($sectionUri, '://')) {
                $uri = $sectionUri;
            } else {
                // Append baseUrl to sectionUri if it is relative
                $uri = $this->baseUrl . ($sectionUri ? '/' . $sectionUri : '');
                if ($absolute) {
                    $uri = $this->baseLocation
                        . ($uri ? '/' . ltrim($uri, '/') : '');
                }
            }
            // Assemble full URI
            $uri .= $path ? '/' . ltrim($path, '/') : '';
        }

        return $uri;
    }
}
