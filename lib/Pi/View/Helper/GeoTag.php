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

use Zend\View\Helper\AbstractHelper;

/**
 * Helper for rendering Geo tags
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
 * @see https://en.wikipedia.org/wiki/Geotagging
 */
class GeoTag extends AbstractHelper
{
    /**
     * Set head Geo-Tags
     *
     * @param string    $latitude      Head geo.position
     * @param string    $longitude     Head geo.position
     * @param string    $placename     Head geo.placename
     * @param string    $region        Head geo.region
     * @return $this
     */
    public function __invoke(
        $latitude,
        $longitude,
        $placename = '',
        $region = ''
    ) {
        // Set geo.position
        if (!empty($latitude) && !empty($longitude)) {
            $position = sprintf('%s; %s', $latitude, $longitude);
            $this->view->headMeta($position, 'ICBM');
            $this->view->headMeta($position, 'geo.position');
        }

        // Set geo.placename
        if (!empty($placename)) {
            $this->view->headMeta($placename, 'geo.placename');
        }

        // Set geo.region
        if (!empty($region)) {
            $this->view->headMeta($region, 'geo.region');
        }   

        return $this;
    }
}