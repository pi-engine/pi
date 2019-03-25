<?php
/**
 * This file is part of Google Map Polyline Encoding Tool library.
 *
 * PHP Version 5.3
 *
 * @category   Mapping
 * @package    Test
 * @subpackage Issue10
 * @author     E. McConville <emcconville@emcconville.com>
 * @license    https://www.gnu.org/licenses/lgpl.html GNU LGPL, version 3
 * @link       https://github.com/emcconville/google-map-polyline-encoding-tool
 */

/**
 * Issue #10
 *
 * Wrong rounding method for google.
 *
 * @category   Mapping
 * @package    Test
 * @subpackage Issue10
 * @author     E. McConville <emcconville@emcconville.com>
 * @license    https://www.gnu.org/licenses/lgpl.html GNU LGPL, version 3
 * @link       https://github.com/emcconville
 *             /google-map-polyline-encoding-tool
 *             /issues/10
 */
class Issue10Test extends PHPUnit_Framework_TestCase
{
    /**
     * Test rounding issues report by issue #10
     *
     * @return NULL
     */
    public function testRounding()
    {
        $originalPoints = array(48.000006, 2.000004,48.00001,2.00000);
        $encoded = Polyline::encode($originalPoints);
        $this->assertEquals('a_~cH_seK??', $encoded);
        $decodedPoints = Polyline::decode($encoded);
        $this->assertTrue($decodedPoints[0] === $decodedPoints[2]);
        $this->assertTrue($decodedPoints[1] === $decodedPoints[3]);
    }
}
