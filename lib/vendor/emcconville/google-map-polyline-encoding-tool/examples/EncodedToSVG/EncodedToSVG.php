<?php
/**
 * Encoded string to SVG example file
 *
 * PHP Version 5.3
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @category  Examples
 * @package   EncodedToSVG
 * @author    E. McConville <emcconville@emcconville.com>
 * @copyright 2014 emcconville
 * @license   http://www.gnu.org/licenses/lgpl.html LGPL v3
 * @version   GIT: $Id:$
 * @link      https://github.com/emcconville/google-map-polyline-encoding-tool
 * @since     v1.2.4
 */


/**
 * Encoded String to SVG example
 *
 * An example class to convert encoded strings to SVG for sample viewing.
 *
 * @category  Examples
 * @package   EncodedToSVG
 * @author    E. McConville <emcconville@emcconville.com>
 * @copyright 2014 emcconville
 * @license   http://www.gnu.org/licenses/lgpl.html LGPL v3
 * @link      https://github.com/emcconville/google-map-polyline-encoding-tool
 * @since     v1.2.4
 */
class EncodedToSVG extends Polyline
{
    /**
     * Decode string and generate a full SVG document.
     *
     * @param string $encoded - Encoded polyline
     *
     * @return string - SVG document
     *
     * @uses DOMDocument
     */
    public static function decodeToSVG( $encoded )
    {
        // Create list of points
        $points = parent::decode($encoded);
        // Grab first pair
        list($x, $y) = self::_shiftPoint($points);
        // Path will need to start by moving to first coordinate.
        $path = sprintf('M %f %f L ', $x, $y);
        // Init bounding box's min & max.
        $minX = $maxX = $x;
        $minY = $maxY = $y;
        while ( $points ) { // This can be simplified with php-5.5's generators.
            list($x, $y) = self::_shiftPoint($points);
            $path .= sprintf('%f %f, ', $x, $y);
            // Grow MBR
            if ($x < $minX) {
                $minX = $x;
            }
            if ($y < $minY) {
                $minY = $y;
            }
            if ($x > $maxX) {
                $maxX = $x;
            }
            if ($y > $maxY) {
                $maxY = $y;
            }
        }
        // Close poylgon
        $path = rtrim($path, ', ');
        $path .= ' Z';
        // Create viewBox from MBR points
        $mbr =  sprintf(
            "%f %f %f %f",
            $minX,
            $minY,
            abs($maxX - $minX),
            abs($maxY - $minY)
        );
        return self::_generateSVG($path, $mbr);
    }

    /**
     * Shift point tuple from start of list.
     *
     * Remember that latitude is Y, and longitude is X on the coordinate system.
     * Depending on your data set, you may need to adjust signing to match
     * hemispheres.
     *
     * @param array &$points - Reference to list
     *
     * @return array - Tuple of (x, y)
     */
    private static function _shiftPoint( &$points )
    {
        $y = array_shift($points);
        $x = array_shift($points);
        return array( $x, $y * -1 );
    }

    /**
     * Turn path & MBR into a valid SVG string
     *
     * @param string $pathData - Raw line data to path element
     * @param string $viewBox  - The known image MBR
     *
     * @return string - SVG
     */
    private static function _generateSVG( $pathData, $viewBox )
    {
        // Build XML
        $dom = new DOMDocument('1.0', 'utf-8');
        $dom->formatOutput = true;
        // Root
        $root = $dom->createElementNS('http://www.w3.org/2000/svg', 'svg');
        $root->appendChild(new DomAttr('version', '1.2'));
        $root->appendChild(new DomAttr('viewBox', $viewBox));
        $root->appendChild(new DomAttr('viewport-fill', 'lightblue'));
        $root->appendChild(new DomAttr('style', 'background-color:lightblue;'));
        // Group
        $g = $dom->createElement('g');
        $g->appendChild(new DomAttr('stroke', 'rgba(0,0,0,0.5)'));
        $g->appendChild(new DomAttr('stroke-width', '0.25%'));
        $g->appendChild(new DomAttr('fill', 'beige'));
        // Path
        $p = $dom->createElement('path');
        $p->appendChild(new DomAttr('d', $pathData));

        // Pull it all together
        $g->appendChild($p);
        $root->appendChild($g);
        $dom->appendChild($root);

        return $dom->saveXML();
    }
}
