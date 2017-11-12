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
use Zend\View\Helper\AbstractHelper;

/**
 * Helper for load Google map
 *
 * Set $locations arrays like this
 *
 *  For point :
 *  $locations = array(
 *      'latitude'  => '', // latitude number
 *      'longitude' => '', // longitude number
 *      'zoom'      => '', // zoom number
 *      'title'     => '', // place title
 *  );
 *
 *  For route :
 *  $locations = array(
 *      'latitude'        => '', // latitude number
 *      'longitude'       => '', // longitude number
 *      'title'           => '', // place title
 *      'final_latitude'  => '', // final latitude number
 *      'final_longitude' => '', // final longitude number
 *      'final_title'     => '', // final place title
 *  );
 *
 *  For list :
 *  $locations = array(
 *      'latitude'  => '', // latitude number
 *      'longitude' => '', // longitude number
 *      'zoom'      => '', // zoom number
 *      'list'      => '', // list of all points , check : http://maplacejs.com/#Locationsdocs
 *  );
 *
 * Usage inside a phtml template
 *
 * ```
 *  // Default mode
 *  $this->googleMap($locations);
 *
 *  // Or specific mode
 *  $this->googleMap($locations, $apiKey);
 *
 *  // Or specific mode
 *  $this->googleMap($locations, $apiKey, $type);
 *
 *  // Or specific mode
 *  $this->googleMap($locations, $apiKey, $type, $htmlClass);
 * ```
 *
 * @see http://www.maplacejs.com
 * @see Google map developer guide
 * @author Hossein Azizabadi <djvoltan@gmail.com>
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class GoogleMap extends AbstractHelper
{
    /**
     * Google map URL
     * @var string
     */
    protected $jsUrl = 'https://maps.googleapis.com/maps/api/js?v=3.exp&callback=initialize';

    /**
     * Load GA scripts
     *
     * @param   array $locations
     * @param   string $apiKey
     * @param   string $type point|route|list
     * @param   array $option Set custom options
     *
     * @return  $this
     */
    public function __invoke(
        $locations,
        $apiKey = '',
        $type = 'point',
        $option = []
    )
    {

        // Set uniq id
        $id = uniqid("google-map-");

        // Set html class
        $htmlClass = empty($option['htmlClass']) ? 'pi-map-canvas' : $option['htmlClass'];

        // Set mapTypeId
        $mapTypeId = empty($option['mapTypeId']) ? 'ROADMAP' : $option['mapTypeId'];
        switch ($mapTypeId) {
            case 'SATELLITE':
                $mapTypeId = 'google.maps.MapTypeId.SATELLITE';
                break;

            case 'HYBRID':
                $mapTypeId = 'google.maps.MapTypeId.HYBRID';
                break;

            case 'TERRAIN':
                $mapTypeId = 'google.maps.MapTypeId.TERRAIN';
                break;

            case 'ROADMAP':
            default:
                $mapTypeId = 'google.maps.MapTypeId.ROADMAP';
                break;
        }

        // Set map info
        switch ($type) {

            case 'routes':

                // Set location array
                $routeLocationScript = [];
                foreach ($locations as $location) {
                    $locationInfo = [
                        'lat'   => $location['lat'],
                        'lon'   => $location['lon'],
                        'title' => $location['title'],
                    ];
                    if (isset($location['stopover']) && !empty($location['stopover'])) {
                        $locationInfo['stopover'] = $location['stopover'];
                    }
                    if (isset($location['visible']) && !empty($location['visible'])) {
                        $locationInfo['visible'] = $location['visible'];
                    }
                    if (isset($location['html']) && !empty($location['html'])) {
                        $locationInfo['html'] = $location['html'];
                    }
                    $routeLocationScript[] = $locationInfo;
                }
                $routeLocationScript = json_encode($routeLocationScript);

                // Set route script
                $routeScript = <<<'EOT'
$(function() {
    var Location = %s;
    
    new Maplace({
        locations: Location,
        map_div: "#%s",
        generate_controls: false,
        show_markers: false,
        type: "directions",
        draggable: true,
        directions_panel: "#route",
        afterRoute: function(distance) {
            $("#km").text(": "+(distance/1000)+"km");
        }
    }).Load();
});
EOT;
                // Set item info on script
                $script = sprintf(
                    $routeScript,
                    $routeLocationScript,
                    $id
                );
                // Set url and key
                $url = "https://maps.googleapis.com/maps/api/js";
                if (!empty($apiKey)) {
                    $url = sprintf('%s?key=%s', $url, $apiKey);
                }

                // Load maplace
                $this->view->js($url);
                $this->view->js(pi::url('static/js/maplace.min.js'));
                $this->view->footScript()->appendScript($script);
                break;

            case 'route':
                // Set route script
                $routeScript = <<<'EOT'
$(function() {
    var Location = [
        {lat: %s, lon: %s, title: "%s"},
        {lat: %s, lon: %s, title: "%s"}
    ];
    
    var noPoi = [
        {
            featureType: "poi.business",
            stylers: [
              { visibility: "off" }
            ]   
        }
    ];
    new Maplace({
        locations: Location,
        map_div: "#%s",
        generate_controls: false,
        show_markers: false,
        type: "directions",
        draggable: true,
        directions_panel: "#route",
        directions_options: {
            travelMode: google.maps.TravelMode.WALKING,
            unitSystem: google.maps.UnitSystem.METRIC,
            optimizeWaypoints: false,
            provideRouteAlternatives: false,
            avoidHighways: false,
            avoidTolls: false
        },
        map_options: {
            scrollwheel: false,
            mapTypeId: %s,
            styles: noPoi
        },
        afterRoute: function(distance) {
            $("#km").text(": "+(distance/1000)+"km");
        }
    }).Load();
});
EOT;
                // Set item info on script
                $script = sprintf(
                    $routeScript,
                    $locations['latitude'],
                    $locations['longitude'],
                    $locations['title'],
                    $locations['final_latitude'],
                    $locations['final_longitude'],
                    $locations['final_title'],
                    $id,
                    $mapTypeId
                );
                // Set url and key
                $url = "https://maps.googleapis.com/maps/api/js";
                if (!empty($apiKey)) {
                    $url = sprintf('%s?key=%s', $url, $apiKey);
                }

                // Load maplace
                $this->view->js($url);
                $this->view->js(pi::url('static/js/maplace.min.js'));
                $this->view->footScript()->appendScript($script);
                break;

            case 'list':
                // Set script
                $listScript = <<<'EOT'
var MaPlace = {};
$(function() {
    var noPoi = [
        {
            featureType: "poi.business",
            stylers: [
              { visibility: "off" }
            ]   
        }
    ];
    var Location = %s;
    var maPlace = new Maplace({
        locations: Location,
        map_div: "#%s",
        controls_title: "%s : ",
        view_all_text: "%s : ",
        map_options: {
            scrollwheel: false,
            set_center: [%s, %s],
            zoom: %s,
            mapTypeId: %s,
            styles: noPoi
            
        }
    }).Load();
    MaPlace.location = Location;
    MaPlace.object = maPlace;
});
EOT;
                // Set item info on script
                $script = sprintf(
                    $listScript,
                    $locations['list'],
                    $id,
                    __('Choose a location'),
                    __('View all'),
                    $locations['latitude'],
                    $locations['longitude'],
                    $locations['zoom'],
                    $mapTypeId
                );

                // Set url and key
                $url = "https://maps.googleapis.com/maps/api/js";
                if (!empty($apiKey)) {
                    $url = sprintf('%s?key=%s', $url, $apiKey);
                }

                // Load maplace
                $this->view->js($url);
                $this->view->js(pi::url('static/js/maplace.min.js'));
                $this->view->footScript()->appendScript($script);
                break;

            case 'point':
            default:
                // Set point script
                $pointScript = <<<'EOT'
function initialize() {
    var noPoi = [
        {
            featureType: "poi.business",
            stylers: [
              { visibility: "off" }
            ]   
        }
    ];
    
    var myLatlng = new google.maps.LatLng(%s, %s);
    var mapOptions = {
        zoom: %s,
        center: myLatlng,
        mapTypeId: %s,
        scrollwheel: false,
        styles: noPoi
    };
    var map = new google.maps.Map(document.getElementById('%s'), mapOptions);
    var marker = new google.maps.Marker({
        position: myLatlng,
        map: map,
        draggable:true,
        title: "%s"
    });
}
EOT;
                // Set item info on script
                $script = sprintf(
                    $pointScript,
                    $locations['latitude'],
                    $locations['longitude'],
                    $locations['zoom'],
                    $mapTypeId,
                    $id,
                    $locations['title']
                );

                // Set url and key
                $url = "https://maps.googleapis.com/maps/api/js?v=3&callback=initialize";
                if (!empty($apiKey)) {
                    $url = sprintf('%s&key=%s', $url, $apiKey);
                }

                // Load script
                $this->view->footScript()->appendScript($script);
                $this->view->footScript()->appendFile($url);
                break;
        }

        // render html
        $htmlTemplate = <<<'EOT'
<div class="pi-map clearfix">
    <div class="thumbnail">
        <div id="%s" class="%s"></div>
    </div>
</div>
EOT;

        $content = sprintf($htmlTemplate, $id, $htmlClass);

        return $content;
    }
}
