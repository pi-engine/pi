<?php
/**
 * Well Known Binary Polyline example file
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
 * @package   WkbPolyline
 * @author    E. McConville <emcconville@emcconville.com>
 * @copyright 2014 emcconville
 * @license   http://www.gnu.org/licenses/lgpl.html LGPL v3
 * @link      https://github.com/emcconville/google-map-polyline-encoding-tool
 * @since     v1.2.4
 */

/**
 * Well Known Binary Polyline example
 *
 * An example class to convert well-known binary files to Google encoded strings
 *
 * @category  Examples
 * @package   WkbPolyline
 * @author    E. McConville <emcconville@emcconville.com>
 * @copyright 2014 emcconville
 * @license   http://www.gnu.org/licenses/lgpl.html LGPL v3
 * @link      https://github.com/emcconville/google-map-polyline-encoding-tool
 * @since     v1.2.4
 */
class WkbPolyline extends Polyline
{
    const ENDIAN_BIG    =  0;
    const ENDIAN_LITTLE =  1;

    protected $fd;
    protected $cursor;
    protected $endianness;

    /**
     * Parse binary file & converts WKB to google encoded string.
     *
     * @param string $filename - The path to the binary file to be encoded.
     *
     * @return string - Encoded string
     */
    public function encodeFromFile($filename)
    {
        $this->fd = fopen($filename, 'rb');
        $points = $this->parseWkb();
        fclose($this->fd);

        return parent::encode($points);
    }

    /**
     * Encoded WKB from blob
     *
     * This method will copy the given blob to memory descriptor. There's better
     * ways to do this.
     *
     * @param string $blob - Binary safe string
     *
     * @return string
     */
    public function encodeFromBlob($blob)
    {
        $this->fd = fopen('php://memory', 'wb');
        fwrite($this->fd, $blob);
        fseek($this->fd, 0);
        $points = $this->parseWkb();
        fclose($this->fd);

        return parent::encode($points);
    }

    /**
     * Extract points from well-known binary
     *
     * @return array - Points found in binary
     */
    protected function parseWkb()
    {
        assert(is_resource($this->fd), "Not a resource");
        // Read first byte to determine endianness.
        $this->endianness = $this->readByte();
        // Get unsigned integer, and convert to vector type.
        $header = $this->readU32() % 1000;
        // This example will only support `Polygon` shapes
        assert($header == 3, "This example only covers polyline shapes");

        $points = array();

        // Iterate over circles
        for ($i=0,$l=$this->readU32(); $i < $l; $i++ ) {
            // Iterate over points
            for ($j=0,$p=$this->readU32(); $j < $p; $j++ ) {
                $points[] = $this->readDouble(); // latitude
                $points[] = $this->readDouble(); // longitude
            }
        }
        return $points;
    }

    /**
     * Read 8 bytes and cast to double. Respects file endianness.
     *
     * @return double
     */
    protected function readDouble()
    {
        $data = $this->chunk(8);
        if ( $this->endianness == self::ENDIAN_BIG ) {
            $data = strrev($data);
        }
        $double = unpack('ddouble', $data);
        return $double['double'];
    }

    /**
     * Read 4 bytes and cast to unsigned integer. Respects file endianness.
     *
     * @return integer
     */
    protected function readU32()
    {
        $format = $this->endianness ? 'Vlong' : 'Nlong';
        $unsignedLong = unpack($format, $this->chunk(4));
        return $unsignedLong['long'];
    }

    /**
     * Read single byte from file descriptor.
     *
     * @return integer - Order of byte.
     */
    protected function readByte()
    {
        return ord($this->chunk());
    }

    /**
     * Pulls data directly from file descriptor by given length.
     *
     * @param integer $size - Default 1
     *
     * @return string - Binary safe string.
     */
    protected function chunk($size=1)
    {
        $this->cursor += $size;
        return fread($this->fd, $size);
    }

}