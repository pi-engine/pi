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

/**
 * Browser API calls
 *
 * Usage examples
 *
 * ```
 *  // Get browser information
 *  // Returns associative array of `browser`, `version`, `platform`
 *  Pi::service('browser')->getBrowser();
 *
 *  // Get browser name
 *  Pi::service('browser')->getBrowser('browser');
 *  // Or
 *  Pi::service('browser')->getName();
 *
 *  // Get browser version
 *  Pi::service('browser')->getBrowser('version');
 *  // Or
 *  Pi::service('browser')->getVersion();
 *
 *  // Check browser
 *  Pi::service('browser')->isBrowser('IE');
 *  Pi::service('browser')->isIe()
 *  // Check browser and version
 *  Pi::service('browser')->isBrowser('IE', '11');
 *  Pi::service('browser')->isBrowser('IE', '11.0');
 *  Pi::service('browser')->isIe('11');
 *  Pi::service('browser')->isIe('11.0');
 * ```
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Browser extends AbstractService
{
    /** @var string HTTP_USER_AGENT */
    protected $userAgent = '';

    /** @var array Browser info */
    protected $browser;

    /**
     * {@inheritDoc}
     */
    public function __construct($options = array())
    {
        parent::__construct($options);
        if (!empty($_SERVER['HTTP_USER_AGENT'])) {
            $this->setUserAgent($_SERVER['HTTP_USER_AGENT']);
        }
    }

    /**
     * Set user_agent content
     *
     * @param string $userAgent
     *
     * @return $this
     */
    public function setUserAgent($userAgent)
    {
        $this->userAgent = $userAgent;

        return $this;
    }

    /**
     * Returns array of all browser info.
     *
     * @param string $key
     *
     * @return array|string
     */
    public function getBrowser($key = '')
    {
        if (null === $this->browser) {
            $browser = get_browser(null, true);
            if (!$browser) {
                $browser = $this->parseUserAgent($this->userAgent);
            }
            $this->browser = $browser;
        }

        $result = $this->browser;
        if ($key) {
            $result = isset($result[$key]) ? $result[$key] : null;
        }

        return $result;
    }

    /**
     * Get browser name
     *
     * @return string
     */
    public function getName()
    {
        return $this->getBrowser('browser');
    }

    /**
     * Get browser version
     *
     * @return string
     */
    public function getVersion()
    {
        return $this->getBrowser('version');
    }

    /**
     * Get browser platform
     *
     * @return string
     */
    public function getPlatform()
    {
        return $this->getBrowser('platform');
    }

    /**
     * Conditional to test for any browser.
     *
     * @param string $name
     * @param string $version
     *
     * @return bool
     */
    public function isBrowser($name, $version = '')
    {
        $result = true;

        $info = $this->getBrowser();
        if (!isset($info['browser'])) {
            $result = false;
        } elseif (false !== (strpos($info['browser'], $name))) {
            if ($version) {
                if (0 !== strpos($info['version'], $version)) {
                    $result = false;
                }
            }
        } else {
            $result = false;
        }

        return $result;
    }

    /**
     * Conditional to test for Chrome.
     *
     * @param string $version
     *
     * @return bool
     */
    public function isChrome($version = '')
    {
        return $this->isBrowser('Chrome', $version);
    }

    /**
     * Conditional to test for Firefox.
     *
     * @param string $version
     *
     * @return bool
     */
    public function isFirefox($version = '')
    {
        return $this->isBrowser('Firefox', $version);
    }

    /**
     * Conditional to test for IE.
     *
     * @param string $version
     *
     * @return bool
     */
    public function isIe($version = '')
    {
        return $this->isBrowser('IE', $version);
    }

    /**
     * Conditional to test for Opera.
     *
     * @param string $version
     *
     * @return bool
     */
    public function isOpera($version = '')
    {
        return $this->isBrowser('Opera', $version);
    }

    /**
     * Conditional to test for Safari.
     *
     * @param string $version
     *
     * @return bool
     */
    public function isSafari($version = '')
    {
        return $this->isBrowser('Safari', $version);
    }

    /**
     * Conditional to test for desktop devices.
     *
     * @return bool
     */
    public function isDesktop()
    {
        return null;
    }

    /**
     * Conditional to test for tablet devices.
     *
     * @return bool
     */
    public function isTablet()
    {
        return null;
    }

    /**
     * Conditional to test for mobile devices.
     *
     * @return bool
     */
    public function isMobile()
    {
        return null;
    }

    /**
     * Conditional to test for iPhone.
     *
     * @param string $version
     *
     * @return bool
     */
    public function isIphone($version = '')
    {
        return null;
    }

    /**
     * Conditional to test for iPad.
     *
     * @param string $version
     *
     * @return bool
     */
    public function isIpad($version = '')
    {
        return null;
    }

    /**
     * Conditional to test for iPod.
     *
     * @param string $version
     *
     * @return bool
     */
    public function isIpod($version = '')
    {
        return null;
    }

    /**
     * Conditional to test for JavaScript support.
     *
     * @return bool
     */
    public function supportsJavascript()
    {
        return null;
    }

    /**
     * Conditional to test for cookie support.
     *
     * @return bool
     */
    public function supportsCookies()
    {
        return null;
    }

    /**
     * Conditional to test for CSS support.
     *
     * @return bool
     */
    public function supportsCss()
    {
        return null;
    }

    /**
     * Parse user agent data
     *
     * @param string|null $userAgent
     *
     * @return array
     */
    public function parseUserAgent($userAgent = null)
    {
        $result = $this->parse_user_agent($userAgent);
        switch ($result['browser']) {
            case 'MSIE':
                $result['browser'] = 'IE';
                break;
            default:
                break;
        }

        return $result;
    }

    /**
     * Parses a user agent string into its important parts
     *
     * @author Jesse G. Donat <donatj@gmail.com>
     * @link   https://github.com/donatj/PhpUserAgent
     * @link   http://donatstudios.com/PHP-Parser-HTTP_USER_AGENT
     *
     * @param string|null $u_agent User agent string to parse or null. Uses $_SERVER['HTTP_USER_AGENT'] on NULL
     *
     * @throws \InvalidArgumentException on not having a proper user agent to parse.
     * @return array an array with browser, version and platform keys
     */
    protected function parse_user_agent( $u_agent = null )
    {
        if( is_null($u_agent) ) {
            if( isset($_SERVER['HTTP_USER_AGENT']) ) {
                $u_agent = $_SERVER['HTTP_USER_AGENT'];
            } else {
                throw new \InvalidArgumentException('parse_user_agent requires a user agent');
            }
        }

        $platform = null;
        $browser  = null;
        $version  = null;

        $empty = array( 'platform' => $platform, 'browser' => $browser, 'version' => $version );

        if( !$u_agent ) return $empty;

        if( preg_match('/\((.*?)\)/im', $u_agent, $parent_matches) ) {

            preg_match_all('/(?P<platform>BB\d+;|Android|CrOS|iPhone|iPad|Linux|Macintosh|Windows(\ Phone)?|Silk|linux-gnu|BlackBerry|PlayBook|Nintendo\ (WiiU?|3DS)|Xbox(\ One)?)
				(?:\ [^;]*)?
				(?:;|$)/imx', $parent_matches[1], $result, PREG_PATTERN_ORDER);

            $priority           = array( 'Android', 'Xbox One', 'Xbox' );
            $result['platform'] = array_unique($result['platform']);
            if( count($result['platform']) > 1 ) {
                if( $keys = array_intersect($priority, $result['platform']) ) {
                    $platform = reset($keys);
                } else {
                    $platform = $result['platform'][0];
                }
            } elseif( isset($result['platform'][0]) ) {
                $platform = $result['platform'][0];
            }
        }

        if( $platform == 'linux-gnu' ) {
            $platform = 'Linux';
        } elseif( $platform == 'CrOS' ) {
            $platform = 'Chrome OS';
        }

        preg_match_all('%(?P<browser>Camino|Kindle(\ Fire\ Build)?|Firefox|Iceweasel|Safari|MSIE|Trident/.*rv|AppleWebKit|Chrome|IEMobile|Opera|OPR|Silk|Lynx|Midori|Version|Wget|curl|NintendoBrowser|PLAYSTATION\ (\d|Vita)+)
			(?:\)?;?)
			(?:(?:[:/ ])(?P<version>[0-9A-Z.]+)|/(?:[A-Z]*))%ix',
            $u_agent, $result, PREG_PATTERN_ORDER);


        // If nothing matched, return null (to avoid undefined index errors)
        if( !isset($result['browser'][0]) || !isset($result['version'][0]) ) {
            return $empty;
        }

        $browser = $result['browser'][0];
        $version = $result['version'][0];

        $find = function ( $search, &$key ) use ( $result ) {
            $xkey = array_search(strtolower($search), array_map('strtolower', $result['browser']));
            if( $xkey !== false ) {
                $key = $xkey;

                return true;
            }

            return false;
        };

        $key = 0;
        if( $browser == 'Iceweasel' ) {
            $browser = 'Firefox';
        } elseif( $find('Playstation Vita', $key) ) {
            $platform = 'PlayStation Vita';
            $browser  = 'Browser';
        } elseif( $find('Kindle Fire Build', $key) || $find('Silk', $key) ) {
            $browser  = $result['browser'][$key] == 'Silk' ? 'Silk' : 'Kindle';
            $platform = 'Kindle Fire';
            if( !($version = $result['version'][$key]) || !is_numeric($version[0]) ) {
                $version = $result['version'][array_search('Version', $result['browser'])];
            }
        } elseif( $find('NintendoBrowser', $key) || $platform == 'Nintendo 3DS' ) {
            $browser = 'NintendoBrowser';
            $version = $result['version'][$key];
        } elseif( $find('Kindle', $key) ) {
            $browser  = $result['browser'][$key];
            $platform = 'Kindle';
            $version  = $result['version'][$key];
        } elseif( $find('OPR', $key) ) {
            $browser = 'Opera Next';
            $version = $result['version'][$key];
        } elseif( $find('Opera', $key) ) {
            $browser = 'Opera';
            $find('Version', $key);
            $version = $result['version'][$key];
        } elseif( $find('Midori', $key) ) {
            $browser = 'Midori';
            $version = $result['version'][$key];
        } elseif( $browser == 'MSIE' || strpos($browser, 'Trident') !== false ) {
            if( $find('IEMobile', $key) ) {
                $browser = 'IEMobile';
            } else {
                $browser = 'MSIE';
                $key     = 0;
            }
            $version = $result['version'][$key];
        } elseif( $find('Chrome', $key) ) {
            $browser = 'Chrome';
            $version = $result['version'][$key];
        } elseif( $browser == 'AppleWebKit' ) {
            if( ($platform == 'Android' && !($key = 0)) ) {
                $browser = 'Android Browser';
            } elseif( strpos($platform, 'BB') === 0 ) {
                $browser  = 'BlackBerry Browser';
                $platform = 'BlackBerry';
            } elseif( $platform == 'BlackBerry' || $platform == 'PlayBook' ) {
                $browser = 'BlackBerry Browser';
            } elseif( $find('Safari', $key) ) {
                $browser = 'Safari';
            }

            $find('Version', $key);

            $version = $result['version'][$key];
        } elseif( $key = preg_grep('/playstation \d/i', array_map('strtolower', $result['browser'])) ) {
            $key = reset($key);

            $platform = 'PlayStation ' . preg_replace('/[^\d]/i', '', $key);
            $browser  = 'NetFront';
        }

        return array( 'platform' => $platform, 'browser' => $browser, 'version' => $version );

    }
}
