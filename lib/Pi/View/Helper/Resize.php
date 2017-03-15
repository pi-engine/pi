<?php

namespace Pi\View\Helper;

use Zend\View\Helper\AbstractHelper;
use Pi\Application\Service\ImageProcessing;
use Imagine\Gd\Imagine;

class Resize extends AbstractHelper
{
    /** @var  array */
    protected $imgParts;
    /** @var  string*/
    protected $commands;

    /** @var  string*/
    protected $cropping;

    protected $imgPath;

    /**
     * @var $quality;
     */
    protected $quality = 90;

    /**
     * @param $imgPath
     * @return $this
     */
    public function __invoke($imgPath, $cropping = null)
    {
        $this->imgPath = $imgPath;
        $this->imgParts = pathinfo($imgPath);
        $this->commands = '';
        $this->cropping = $cropping;

        return $this;
    }

    /**
     * @param $width
     * @param $height
     * @return $this
     */
    public function thumb($width, $height)
    {
        $this->commands .= '$thumb,' . $width . ',' . $height;

        return $this;
    }

    /**
     * @param $width
     * @param $height
     * @return $this
     */
    public function thumbcrop($width, $height)
    {
        $this->commands .= '$thumbcrop,' . $width . ',' . $height;

        return $this;
    }

    /**
     * @param $width
     * @param $height
     * @return $this
     */
    public function resize($width, $height)
    {
        $this->commands .= '$resize,' . $width . ',' . $height;

        return $this;
    }

    /**
     * @param $width
     * @param $height
     * @return $this
     */
    public function quality($value = null)
    {
        if($value !== null && is_numeric($value)){
            $this->commands .= '$quality,' . $value;
            $this->quality = $value;
        }

        return $this;
    }

    /**
     * @return $this
     */
    public function grayscale()
    {
        $this->commands .= '$grayscale';

        return $this;
    }

    /**
     * @return $this
     */
    public function negative()
    {
        $this->commands .= '$negative';

        return $this;
    }

    /**
     * @param $correction
     * @return $this
     */
    public function gamma($correction)
    {
        $this->commands .= '$gamma,' . $correction;

        return $this;
    }

    /**
     * @param $hexColor
     * @return $this
     */
    public function colorize($hexColor)
    {
        $this->commands .= '$colorize,' . $hexColor;

        return $this;
    }

    /**
     * @return $this
     */
    public function sharpen()
    {
        $this->commands .= '$sharpen';

        return $this;
    }

    /**
     * @param null $sigma
     * @return $this
     */
    public function blur($sigma = null)
    {
        $this->commands .= '$blur' . ($sigma !== null ? ',' . $sigma : '');

        return $this;
    }

    /**
     * @param null $text
     * @param null $backgroundColor
     * @param null $color
     * @param null $width
     * @param null $height
     * @return $this
     */
    public function x404($text = null, $backgroundColor = null, $color = null, $width = null, $height = null)
    {
        $this->commands .= '$404'
            . ($text !== null ? ',' . self::encode($text) : '')
            . ($backgroundColor !== null ? ',' . $backgroundColor : '')
            . ($color !== null ? ',' . $color : '')
            . ($width !== null ? ',' . $width : '')
            . ($height !== null ? ',' . $height : '');

        return $this;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        if($this->commands == ''){
            return \Pi::url($this->imgPath);
        }

        $options = array();

        $options['quality'] = $this->quality;

        try{
            $file = ($this->imgParts['dirname'] && $this->imgParts['dirname'] !== '.' ? $this->imgParts['dirname'] . '/' : '') . $this->imgParts['filename'];

            if(!isset($this->imgParts['extension']) || $this->imgParts['extension'] == ''){
                throw new \Exception('No extension found');
            }

            $targetExtension = $this->imgParts['extension'];

            $source = ''
                . $file
                . '.' . $targetExtension;

            if (!file_exists($source)) {
                $source = null;
                $targetExtension = '404.' . $targetExtension;
            }

            $target = 'upload/media/processed/'
                . $this->commands . '/'
                . str_replace('upload/media/original', '', $file)
                . '.' . $targetExtension;

            if(!is_file($target))
            {
                ini_set("memory_limit","512M");

                $imagine = new Imagine();
                $imageProcessing = new ImageProcessing($imagine);

                if ($source) {
                    $imageProcessing->process($source, $target, preg_replace('#^\$#','',$this->commands), $this->cropping, $options);

                } else {
                    $imageProcessing->process404($target, preg_replace('#^\$#','',$this->commands));
                }
            }
        }
        catch(\Exception $e)
        {
            return '';
        }

        $filepath = '/upload/media/processed/' . $this->commands . '/' . str_replace('upload/media/original/', '', $file) . '.' . $targetExtension;

        return $filepath;
    }

    /**
     * base64 encode
     *
     * @param string $data data to encode
     * @return string encoded data
     */
    public static function encode($data)
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
}
