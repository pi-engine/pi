<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
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
    protected $jsUrl = 'https://maps.googleapis.com/maps/api/js';

    /**
     * Load GA scripts
     *
     * @param   array   $locations
     * @param   string  $apiKey
     * @param   string  $type       point|route|list
     * @param   string  $htmlClass  Set custom html ID
     *
     * @return  $this
     */
    public function __invoke(
    	$locations,
    	$apiKey = '',
    	$type = 'point',
    	$htmlClass = 'pi-map-canvas'
    ) {
        
        // Set uniq id
    	$id = uniqid("google-map-");

        // Set map info
		switch ($type) {

			case 'route':
        		// Set route script  
        		$routeScript =<<<'EOT'
$(function() {
    var Location = [
        {lat: %s, lon: %s, title: "%s"},
        {lat: %s, lon: %s, title: "%s"}
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
        afterRoute: function(distance) {
            $("#km").text(": "+(distance/1000)+"km");
        }
    }).Load(); 
});
EOT;
				// Set item info on script
				$script =  sprintf(
    				$routeScript,
        			$locations['latitude'],
        			$locations['longitude'],
        			$locations['title'],
        			$locations['final_latitude'],
        			$locations['final_longitude'],
        			$locations['final_title'],
        			$id
    			);
                // Load maplace
    			$this->view->js(pi::url('static/js/maplace.min.js'));
				break;

			case 'list':
        		// Set script  
        		$listScript =<<<'EOT'
$(function() {
    var Location = [%s];
    new Maplace({
        locations: Location,
        map_div: "#%s",
        controls_title: "%s : ",
        view_all_text: "%s : ",
        map_options: {
            set_center: [%s, %s],
            zoom: %s,
        }
    }).Load();
});
EOT;
				// Set item info on script
				$script =  sprintf(
                    $listScript,
                    $locations['list'], 
                    $id,
                    __('Choose a location'), 
                    __('View all'), 
        			$locations['latitude'],
        			$locations['longitude'],
                    $locations['zoom']
                );
                // Load maplace
    			$this->view->js(pi::url('static/js/maplace.min.js'));
				break;
			
			case 'point':
			default:
        		// Set point script  
        		$pointScript =<<<'EOT'
var myLatlng = new google.maps.LatLng(%s, %s);
var mapOptions = {
    zoom: %s,
    center: myLatlng
}
var map = new google.maps.Map(document.getElementById("%s"), mapOptions);
var marker = new google.maps.Marker({
    position: myLatlng,
    map: map,
    draggable:true,
    title: "%s"
});
EOT;
			    // Set item info on script
				$script =  sprintf(
    				$pointScript,
        			$locations['latitude'],
        			$locations['longitude'],
        			$locations['zoom'],
        			$id,
        			$locations['title']
    			);
				break;
		}
       
		// Set url and key
		if (!empty($apiKey)) {
			$this->jsUrl = sprintf('%s?key=%s', $this->jsUrl, $apiKey);
		}

        // Load script
        $this->view->headScript()->prependFile($this->jsUrl);
        $this->view->footScript()->appendScript($script);

        // render html
        $htmlTemplate =<<<'EOT'
<div class="thumbnail">
	<div id="%s" class="%s"></div>
</div>
EOT;
       
        $content = sprintf($htmlTemplate, $id, $htmlClass);

        return $content;
    }
}