<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 * @package         View
 */

namespace Pi\View\Helper;

use Pi;
use Zend\View\Helper\AbstractHelper;

/**
 * Helper for render Geo tags
 *
 * Usage inside a phtml template
 *
 * ```
 *  $this->geoTag($latitude, $longitude);
 *  $this->geoTag($latitude, $longitude, $placename);
 *  $this->geoTag($latitude, $longitude, $placename, $region);
 * ```
 *
 * @author Hossein Azizabadi <azizabadi@faragostaresh.com>
 */
class GeoTag extends AbstractHelper
{
    /**
     * Set head Geo-Tags
     *
     * @param string        $latitude      Head geo.position
     * @param string        $longitude     Head geo.position
     * @param string|null   $placename     Head geo.placename
     * @param string|null   $region        Head geo.region
     * @return $this
     */
    public function __invoke(
    	$latitude,
    	$longitude,
    	$placename = null,
    	$region = null
    ) {     

        // Set geo.position
        if (!empty($latitude) && !empty($longitude)) {
            $position = sprintf('%s; %s', $latitude, $longitude);
            $this->view->headMeta()->__invoke(
                $position,
                'ICBM'
            );
            $this->view->headMeta()->__invoke(
                $position,
                'geo.position'
            );  
        }

        // Set geo.placename
        if (isset($placename) && !empty($placename)) {
            $this->view->headMeta()->__invoke(
                $placename,
                'geo.placename'
            );
        }

        // Set geo.region
        if (isset($region) && !empty($region)) {
            $this->view->headMeta()->__invoke(
                $region,
                'geo.region'
            );
        }   

    	return $this;
    }
}