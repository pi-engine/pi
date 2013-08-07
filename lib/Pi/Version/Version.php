<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Pi\Version;

use Zend\Version\Version as ZendVersion;
use Zend\Json\Json;

/**
 * Class to store and retrieve Pi Engine version.
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 * @see http://semver.org/ for semantic versioning
 * @see Zend\Version\Version  Class to store and retrieve the version of
 *      Zend Framework.
 */
class Version
{
    /**
     * Pi Engine version identification - see compareVersion()
     * @var string
     * @see http://semver.org/ for semantic versioning
     */
    const VERSION = '2.3.0-dev';

    /**
     * The latest stable version Pi Engine available
     * @var string
     */
    protected static $latestVersion;

    /**
     * The latest master commit number from github
     * @var string
     */
    protected static $latestCommit;

    /**
     * API URL to retrieve latest commit from Github
     * @var string
     */
    protected static $githubApiCommit =
        'https://api.github.com/repos/pi-engine/pi/git/refs/heads';

    /**
     * API URL to retrieve release tags from Github
     * @var string
     */
    protected static $githubApiRelease =
        'https://api.github.com/repos/pi-engine/pi/git/refs/tags/release-';

    /**
     * API URL to retrieve latest Pi release
     * @var string
     */
    protected static $piApiRelease = '';

    /**
     * Get version number
     *
     * @param string $service
     * @return string
     */
    public static function version($service = 'PI')
    {
        $version = '';
        switch (strtoupper($service)) {
            // Zend Framework version
            case 'ZEND':
                $version = ZendVersion::VERSION;
                break;
            // Full version: Pi version plush Zend version as build metadata
            case 'FULL':
                $version = static::VERSION . '+' . ZendVersion::VERSION;
                break;
            // Pi Version
            case 'PI':
            default:
                $version = static::VERSION;
                break;
        }

        return strtolower($version);
    }

    /**
     * Compare the specified Pi Engine version string $version
     * with the current Pi\Version::VERSION of Pi Engine.
     *
     * @param  string  $version  A version string (e.g. "0.7.1").
     * @return int
     * @see http://www.php.net/manual/en/function.version-compare.php
     */
    public static function compareVersion($version)
    {
        $version = strtolower($version);
        $version = preg_replace('/(\d)pr(\d?)/', '$1a$2', $version);

        return version_compare($version, strtolower(static::VERSION));
    }

    /**
     * Fetches the version of the latest stable release.
     *
     * By Default, this uses the GitHub API (v3) and only returns refs that
     * begin with 'tags/release-'. Because GitHub returns the refs in
     * alphabetical order, we need to reduce the array to a single value,
     * comparing the version numbers with version_compare().
     *
     * If $service is set to VERSION_SERVICE_PI this will fall back to
     * calling the classic style of version retreival.
     *
     *
     * @see http://developer.github.com/v3/git/refs/#get-all-references
     * @link https://api.github.com/repos/pi-engine/pi/git/refs/tags/release-
     * @param string $service Version Service with which to retrieve version
     * @return string
     */
    public static function getLatest($service = 'PI')
    {
        if (null === static::$latestVersion) {
            static::$latestVersion = false;
            $service = strtoupper($service);
            if ($service == 'GITHUB') {
                $url  = static::$githubApiRelease;

                $apiResponse = Json::decode(
                    file_get_contents($url),
                    Json::TYPE_ARRAY
                );

                // Simplify the API response into a simple array of
                // version numbers
                // Reliable because we're filtering on 'refs/tags/release-'
                $tags = array_map(function ($tag) {
                    return substr($tag['ref'], 18);
                }, $apiResponse);

                // Fetch the latest version number from the array
                static::$latestVersion = array_reduce(
                    $tags,
                    function ($a, $b) {
                        return version_compare($a, $b, '>') ? $a : $b;
                    }
                );
            } elseif ($service == 'PI') {
                $handle = fopen(static::$piApiRelease, 'r');
                if (false !== $handle) {
                    static::$latestVersion = stream_get_contents($handle);
                    fclose($handle);
                }
            }
        }

        return static::$latestVersion;
    }

    /**
     * Returns true if the running version of Pi Engine is
     * the latest than the latest tag on GitHub,
     * which is returned by static::getLatest().
     *
     * @return bool
     */
    public static function isLatest()
    {
        return static::compareVersion(static::getLatest()) < 1;
    }

    /**
     * Fetches the last github commit hash number
     *
     * @see http://developer.github.com/v3/git/refs/#get-a-reference
     * @link https://api.github.com/repos/pi-engine/pi/git/refs/heads
     * @return array|false
     */
    public static function getLatestCommit()
    {
        if (null === static::$latestCommit) {
            static::$latestCommit = false;
            $url  = static::$githubApiCommit;

            $apiResponse = Json::decode(
                file_get_contents($url),
                Json::TYPE_ARRAY
            );
            $latestCommit = $apiResponse[0];
            static::$latestCommit = array(
                'commit'    => $latestCommit['object']['sha'],
                'url'       => $latestCommit['object']['url'],
            );
        }

        return static::$latestCommit;
    }
}
