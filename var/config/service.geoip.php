<?php
/**
 * GeoIp service configuration
 */

return array(
    'database'  => array(
        // Database for country, download from http://geolite.maxmind.com/download/geoip/database/GeoLite2-Country.mmdb.gz
        'country'   => 'config/geoip/GeoLite2-Country.mmdb',

        // Database for city, download from http://geolite.maxmind.com/download/geoip/database/GeoLite2-City.mmdb.gz
        'city'      => 'config/geoip/GeoLite2-City.mmdb',
    ),
);
