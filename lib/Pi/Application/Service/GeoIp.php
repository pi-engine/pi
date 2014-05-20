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

use Pi;
use GeoIp2\Database\Reader;

/*
 * Geo IP location lookup
 *
 * To enable the service, dedicated database is required, available at http://geolite.maxmind.com
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 * @see var/config/geoip/README.md
 * @see https://github.com/maxmind/GeoIP2-php
 * @see http://www.php.net/manual/en/book.geoip.php
 */
class GeoIp extends AbstractService
{
    /** {@inheritDoc} */
    protected $fileIdentifier = 'geoip';

    /**
     * Loads database reader
     *
     * @param string $database
     * @param string $locale
     *
     * @return Reader
     */
    public function load($database, $locale = '')
    {
        $locale = $locale ?: Pi::config('locale');
        if ($pos = strpos($locale, '-')) {
            $locale = substr($locale, 0, $pos) . '-' . strtoupper(substr($locale, $pos + 1));
        }
        $locales = array($locale);
        if ('en' != $locale) {
            $locales[] = 'en';
        }
        try {
            $reader = new Reader(Pi::path($this->getOption('database', $database)), $locales);
        } catch (\Exception $e) {
            $reader = false;
        }

        return $reader;
    }

    /**
     * Get geo location data
     *
     * @param string $ip
     * @param string $attribute
     * @param string $locale
     *
     * @return array
     */
    public function get($ip, $attribute = '', $locale = '')
    {
        if ('continent' == $attribute) {
            $result = $this->country($ip, $attribute, $locale);
        } else {
            $result = $this->city($ip, $attribute, $locale);
        }

        return $result;
    }

    /**
     * Get geo location city level data
     *
     * @param string $ip
     * @param string $attribute
     * @param string $locale
     *
     * @return array
     */
    public function city($ip, $attribute = '', $locale = '')
    {
        $reader = $this->load('city', $locale);
        if (!$reader) {
            return false;
        }

        $result = array();
        try {
            $record = $reader->city($ip);
        } catch (\Exception $e) {
            return false;
        }
        $attr = $attribute;
        if (!$attr || 'country' == $attribute) {
            $result['country'] = array(
                'code'  => $record->country->isoCode,
                'name'  => $record->country->name,
            );
            $attribute = '';
        }
        if (!$attr || 'subdivision' == $attribute) {
            $result['subdivision'] = array(
                'code'  => $record->mostSpecificSubdivision->isoCode,
                'name'  => $record->mostSpecificSubdivision->name,
            );
            $attribute = '';
        }
        if (!$attr || 'city' == $attribute) {
            $result['city'] = array(
                'name'  => $record->city->name,
            );
            $attribute = '';
        }
        if (!$attr || 'postal' == $attribute) {
            $result['postal'] = array(
                'code'  => $record->postal->code,
            );
            $attribute = '';
        }
        if (!$attr || 'location' == $attribute) {
            $result['location'] = array(
                'latitude'  => $record->location->latitude,
                'longitude' => $record->location->longitude,
                'timezone'  => $record->location->timeZone,
            );
            $attribute = '';
        }
        if ('traits' == $attribute) {
            foreach (array(
                         'autonomousSystemNumber',
                         'autonomousSystemOrganization',
                         'domain',
                         'isAnonymousProxy',
                         'isSatelliteProvider',
                         'isp',
                         'ipAddress',
                         'organization',
                         'userType'
                     ) as $param) {
                if (isset($record->traits->{$param})) {
                    $result['traits'][$param] = $record->traits->{$param};
                }
            }
            $attribute = '';
        }

        if ($attr) {
            $result = $result[$attr];
        }

        return $result;
    }

    /**
     * Get geo location country level data
     *
     * @param string $ip
     * @param string $attribute
     * @param string $locale
     *
     * @return array
     */
    public function country($ip, $attribute = '', $locale = '')
    {
        $reader = $this->load('country', $locale);
        if (!$reader) {
            return false;
        }

        try {
            $record = $reader->city($ip);
        } catch (\Exception $e) {
            return false;
        }
        $result = array();
        if (!$attribute || 'country' == $attribute) {
            $result['country'] = array(
                'code'  => $record->country->isoCode,
                'name'  => $record->country->name,
            );
        }
        if (!$attribute || 'continent' == $attribute) {
            $result['continent'] = array(
                'code'  => $record->continent->code,
                'name'  => $record->continent->name,
            );
        }
        if ($attribute) {
            $result = $result[$attribute];
        }

        return $result;
    }
}
