# Database for GeoIP service

* To enable local GeoIP service, download database for city and country respectively from
  * `http://geolite.maxmind.com/download/geoip/database/GeoLite2-Country.mmdb.gz`
  * `http://geolite.maxmind.com/download/geoip/database/GeoLite2-City.mmdb.gz`
* Unzip the files and locate in
  * `config/geoip/GeoLite2-Country.mmdb`
  * `config/geoip/GeoLite2-City.mmdb`
* Make sure `service.geoip.php` is specified correctly
```
    'database'  => array(
        // Database for country, download from http://geolite.maxmind.com/download/geoip/database/GeoLite2-Country.mmdb.gz
        'country'   => 'config/geoip/GeoLite2-Country.mmdb',

        // Database for city, download from http://geolite.maxmind.com/download/geoip/database/GeoLite2-City.mmdb.gz
        'city'      => 'config/geoip/GeoLite2-City.mmdb',
    ),
```