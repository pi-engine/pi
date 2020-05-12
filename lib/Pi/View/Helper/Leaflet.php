<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 * @package         View
 */

namespace Pi\View\Helper;

use Pi;
use Laminas\View\Helper\AbstractHelper;

/**
 * Helper for load Leaflet
 * Map, points and ect : https://leafletjs.com
 * Routing : http://www.liedman.net/leaflet-routing-machine
 * Open Street Map tile server needed : https://switch2osm.org
 * Open Source Routing Machine needed : http://project-osrm.org
 *
 *  // map
 *  $params = [
 *      'type'      => 'map',
 *      'latitude'  => '35.708236',
 *      'longitude' => '51.383455',
 *      'zoom'      => '12',
 *  ];
 *
 *  // point
 *  $params = [
 *      'type'      => 'point',
 *      'latitude'  => '35.708236',
 *      'longitude' => '51.383455',
 *      'zoom'      => '12',
 *      'title'     => 'This is test point'
 *  ];
 *
 *  // routing
 *  $params = [
 *      'type'   => 'routing',
 *      'points' => [
 *          [
 *              'latitude'  => '35.77339',
 *              'longitude' => '51.418308',
 *          ],
 *          [
 *              'latitude'  => '35.786196',
 *              'longitude' => '51.445039',
 *          ],
 *      ],
 *  ];
 *
 *  // points
 *  $params = [
 *      'type'      => 'points',
 *      'latitude'  => '35.77339',
 *      'longitude' => '51.418308',
 *      'zoom'      => '13',
 *      'points'    => [
 *          [
 *              'title'     => 'Test 1',
 *              'latitude'  => '35.77339',
 *              'longitude' => '51.418308',
 *          ],
 *          [
 *              'title'     => 'Test 2',
 *              'latitude'  => '35.786196',
 *              'longitude' => '51.445039',
 *          ],
 *      ],
 *  ];
 *
 *  // geoJson
 *  $params = [
 *      'type'           => 'geoJson',
 *      'latitude'       => '35.77339',
 *      'longitude'      => '51.418308',
 *      'zoom'           => '13',
 *      'geoJsonFeature' => 'GEO_JSON_CODE',
 *  ];
 *
 *  // geoJsonAjax
 *  $params = [
 *      'type'      => 'geoJsonAjax',
 *      'latitude'  => '35.77339',
 *      'longitude' => '51.418308',
 *      'zoom'      => '13',
 *      'ajaxUrl'   => 'GEO_JSON_URL',
 *  ];
 *
 *  // script
 *  $script = <<<'EOT'
 *  map.on('click', function(e) {
 *      alert("Lat, Lon : " + e.latlng.lat + ", " + e.latlng.lng);
 *  });
 *  EOT;
 *
 *  $params = [
 *      'type'      => 'script',
 *      'latitude'  => '35.708236',
 *      'longitude' => '51.383455',
 *      'zoom'      => '12',
 *      'script'    => $script,
 *  ];
 *
 * $this->leaflet($params);
 *
 * @author Hossein Azizabadi <azizabadi@faragostaresh.com>
 */

class Leaflet extends AbstractHelper
{
    /**
     * Load Leaflet scripts
     *
     * @param   array $params
     *
     * @return  $this
     */
    public function __invoke($params)
    {
        // Set id
        $params['htmlId'] = isset($params['htmlId']) ? $params['htmlId'] : uniqid("map-");

        // Set html class
        $params['htmlClass'] = isset($params['htmlClass']) ? $params['htmlClass'] : 'pi-map-canvas';

        // Set copyRight
        $params['copyright'] = isset($params['copyright']) ? $params['copyright'] : sprintf(
            '<a href="%s">%s</a>', Pi::url(), Pi::config('sitename')
        );

        // Set map url
        $params['mapUrl'] = isset($params['mapUrl']) ? $params['mapUrl'] : 'https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png';

        // Set routing url
        $params['routingUrl'] = isset($params['routingUrl']) ? $params['routingUrl'] : 'https://router.project-osrm.org/route/v1';

        // Set map info
        switch ($params['type']) {
            default:
            case 'map':
                // Set point script
                $htmlScript
                    = <<<'EOT'
let map = L.map('%s', {center: [%s, %s],zoom: %s});
L.tileLayer('%s', {attribution: '%s', maxZoom: 18}).addTo(map);
EOT;
                // Set item info on script
                $script = sprintf(
                    $htmlScript,
                    $params['htmlId'],
                    $params['latitude'],
                    $params['longitude'],
                    $params['zoom'],
                    $params['mapUrl'],
                    $params['copyright']
                );

                // Load js and css
                $this->view->css(pi::url('static/vendor/leaflet/leaflet.css'));
                $this->view->footScript()->appendFile(pi::url('static/vendor/leaflet/leaflet.js'));
                $this->view->footScript()->appendScript($script);
                break;

            case 'point':
                // Set point script
                $htmlScript
                    = <<<'EOT'
let map = L.map('%s', {center: [%s, %s],zoom: %s});
L.tileLayer('%s', {attribution: '%s', maxZoom: 18}).addTo(map);
L.marker([%s, %s]).addTo(map).bindPopup('%s').openPopup();
EOT;
                // Set item info on script
                $script = sprintf(
                    $htmlScript,
                    $params['htmlId'],
                    $params['latitude'],
                    $params['longitude'],
                    $params['zoom'],
                    $params['mapUrl'],
                    $params['copyright'],
                    $params['latitude'],
                    $params['longitude'],
                    $params['title']
                );

                // Load js and css
                $this->view->css(pi::url('static/vendor/leaflet/leaflet.css'));
                $this->view->footScript()->appendFile(pi::url('static/vendor/leaflet/leaflet.js'));
                $this->view->footScript()->appendScript($script);
                break;

            case 'points':
                // Set point script
                $htmlScript
                    = <<<'EOT'
let planes = %s;
let map = L.map('%s', {center: [%s, %s],zoom: %s});
L.tileLayer('%s', {attribution: '%s', maxZoom: 18}).addTo(map);
for (let i = 0; i < planes.length; i++) {
    marker = new L.marker([planes[i].latitude,planes[i].longitude]).bindPopup(planes[i].title).addTo(map);
}
EOT;
                // Set item info on script
                $script = sprintf(
                    $htmlScript,
                    json_encode($params['points']),
                    $params['htmlId'],
                    $params['latitude'],
                    $params['longitude'],
                    $params['zoom'],
                    $params['mapUrl'],
                    $params['copyright']
                );

                // Load js and css
                $this->view->css(pi::url('static/vendor/leaflet/leaflet.css'));
                $this->view->footScript()->appendFile(pi::url('static/vendor/leaflet/leaflet.js'));
                $this->view->footScript()->appendScript($script);
                break;

            case 'routing':
                // Make route list
                $latLng = [];
                foreach ($params['points'] as $point) {
                    $latLng[] = sprintf('L.latLng(%s, %s)', $point['latitude'], $point['longitude']);
                }
                $latLng = implode(',', $latLng);

                // Set point script
                $htmlScript
                    = <<<'EOT'
let map = L.map('%s');
L.tileLayer('%s', {attribution: '%s', maxZoom: 18}).addTo(map);
L.Routing.control({waypoints: [%s],  routeWhileDragging: true, serviceUrl: '%s', createMarker: function() { return null; }}).addTo(map);
EOT;
                // Set item info on script
                $script = sprintf(
                    $htmlScript,
                    $params['htmlId'],
                    $params['mapUrl'],
                    $params['copyright'],
                    $latLng,
                    $params['routingUrl']
                );

                // Load js and css
                $this->view->css(pi::url('static/vendor/leaflet/leaflet.css'));
                $this->view->css(pi::url('static/vendor/leaflet/plugin/routing/leaflet-routing-machine.css'));
                $this->view->footScript()->appendFile(pi::url('static/vendor/leaflet/leaflet.js'));
                $this->view->footScript()->appendFile(pi::url('static/vendor/leaflet/plugin/routing/leaflet-routing-machine.js'));
                $this->view->footScript()->appendScript($script);
                break;

            case 'geoJson':
                // Set point script
                $htmlScript
                    = <<<'EOT'
let map = L.map('%s', {center: [%s, %s],zoom: %s});
L.tileLayer('%s', {attribution: '%s', maxZoom: 18}).addTo(map);
L.geoJSON(%s).addTo(map);
EOT;
                // Set item info on script
                $script = sprintf(
                    $htmlScript,
                    $params['htmlId'],
                    $params['latitude'],
                    $params['longitude'],
                    $params['zoom'],
                    $params['mapUrl'],
                    $params['copyright'],
                    $params['geoJsonFeature']
                );

                // Load js and css
                $this->view->css(pi::url('static/vendor/leaflet/leaflet.css'));
                $this->view->footScript()->appendFile(pi::url('static/vendor/leaflet/leaflet.js'));
                $this->view->footScript()->appendScript($script);
                break;

            case 'geoJsonAjax':
                // Set point script
                $htmlScript
                    = <<<'EOT'
$.getJSON("%s", function(data) {
	let map = L.map('%s', {center: [%s, %s],zoom: %s});
	L.tileLayer('%s', {attribution: '%s', maxZoom: 18}).addTo(map);
	L.geoJSON(data).addTo(map);
});
EOT;
                // Set item info on script
                $script = sprintf(
                    $htmlScript,
                    $params['ajaxUrl'],
                    $params['htmlId'],
                    $params['latitude'],
                    $params['longitude'],
                    $params['zoom'],
                    $params['mapUrl'],
                    $params['copyright']
                );

                // Load js and css
                $this->view->css(pi::url('static/vendor/leaflet/leaflet.css'));
                $this->view->footScript()->appendFile(pi::url('static/vendor/leaflet/leaflet.js'));
                $this->view->footScript()->appendScript($script);
                break;

            case 'script':
                // Set point script
                $htmlScript
                    = <<<'EOT'
var map = L.map('%s', {center: [%s, %s],zoom: %s});
L.tileLayer('%s', {attribution: '%s', maxZoom: 18}).addTo(map);
%s
EOT;
                // Set item info on script
                $script = sprintf(
                    $htmlScript,
                    $params['htmlId'],
                    $params['latitude'],
                    $params['longitude'],
                    $params['zoom'],
                    $params['mapUrl'],
                    $params['copyright'],
                    $params['script']
                );

                // Load js and css
                if (isset($params['supportRouting']) && $params['supportRouting']) {
                    $this->view->css(pi::url('static/vendor/leaflet/leaflet.css'));
                    $this->view->css(pi::url('static/vendor/leaflet/plugin/routing/leaflet-routing-machine.css'));
                    $this->view->footScript()->appendFile(pi::url('static/vendor/leaflet/leaflet.js'));
                    $this->view->footScript()->appendFile(pi::url('static/vendor/leaflet/plugin/routing/leaflet-routing-machine.js'));
                    $this->view->footScript()->appendScript($script);
                } else {
                    $this->view->css(pi::url('static/vendor/leaflet/leaflet.css'));
                    $this->view->footScript()->appendFile(pi::url('static/vendor/leaflet/leaflet.js'));
                    $this->view->footScript()->appendScript($script);
                }
                break;

            case 'empty':
                $this->view->css(pi::url('static/vendor/leaflet/leaflet.css'));
                $this->view->footScript()->appendFile(pi::url('static/vendor/leaflet/leaflet.js'));
                break;
        }

        // Set html template
        $htmlTemplate
            = <<<'EOT'
<div class="pi-map clearfix">
    <div class="thumbnail">
        <div id="%s" class="%s"></div>
    </div>
</div>
EOT;

        // render content
        $content = sprintf($htmlTemplate, $params['htmlId'], $params['htmlClass']);

        return $content;
    }
}