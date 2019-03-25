<?php
/**
 * This file is part of Google Map Polyline Encoding Tool library.
 *
 * PHP Version 5.3
 *
 * @category   Mapping
 * @package    Test
 * @subpackage Precission
 * @author     E. McConville <emcconville@emcconville.com>
 * @license    https://www.gnu.org/licenses/lgpl.html GNU LGPL, version 3
 * @link       https://github.com/emcconville/google-map-polyline-encoding-tool
 */

/**
 * Extended Polyline
 *
 * Adjust precision by extending the base object, and altering the precision
 * property.
 *
 * @category   Mapping
 * @package    Test
 * @subpackage PrecisionPolyline
 * @author     E. McConville <emcconville@emcconville.com>
 * @license    https://www.gnu.org/licenses/lgpl.html GNU LGPL, version 3
 * @link       https://github.com/emcconville/google-map-polyline-encoding-tool
 */
class PrecisionPolyline extends Polyline
{
    protected static $precision = 6;
}

/**
 * Extended Polyline
 *
 * Adjust precision by extending the base object, and altering the precision
 * property.
 *
 * @category   Mapping
 * @package    Test
 * @subpackage PrecisionTest
 * @author     E. McConville <emcconville@emcconville.com>
 * @license    https://www.gnu.org/licenses/lgpl.html GNU LGPL, version 3
 * @link       https://github.com/emcconville/google-map-polyline-encoding-tool
 */
class PrecisionTest extends PHPUnit_Framework_TestCase
{
    protected $encoded = 'q}~~|AdshNkSsBid@eGqBlm@yKhj@bA?';
    protected $points = array(
        49.283049, -0.250691,
        49.283375, -0.250633,
        49.283972, -0.250502,
        49.284029, -0.251245,
        49.284234, -0.251938,
        49.284200, -0.251938
    );

    /**
     * Verify encoding is working as expected.
     *
     * @covers Polyline::encode
     * @covers Polyline::flatten
     *
     * @return NULL
     */
    public function testEncodePrecision()
    {
        $this->assertEquals(
            $this->encoded,
            PrecisionPolyline::encode($this->points)
        );
    }

    /**
     * Verify decoding is working as expected.
     *
     * @covers Polyline::decode
     *
     * @return NULL
     */
    public function testDecodePrecision()
    {
        $this->assertEquals(
            $this->points,
            PrecisionPolyline::decode($this->encoded)
        );
    }
}
