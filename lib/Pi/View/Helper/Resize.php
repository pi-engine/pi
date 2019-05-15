<?php

namespace Pi\View\Helper;

use Imagine\Gd\Imagine;
use Pi;
use Pi\Application\Service\ImageProcessing;
use Zend\View\Helper\AbstractHelper;

class Resize extends AbstractHelper
{
    /** @var  array */
    protected $imgParts;
    /** @var  string */
    protected $commands;

    /**
     * @var
     */
    protected $timestamp = null;

    /** @var  string*/
    protected $cropping;

    protected $imgPath;

    protected $defaultSizes
        = [
            'large'     => [
                'width'  => 1024,
                'height' => 768,
            ],
            'item'      => [
                'width'  => 800,
                'height' => 600,
            ],
            'medium'    => [
                'width'  => 640,
                'height' => 480,
            ],
            'thumbnail' => [
                'width'  => 320,
                'height' => 240,
            ],
        ];

    /**
     * @var $quality ;
     */
    protected $quality = 90;

    /**
     * @param $imgPath
     * @return $this
     */
    public function __invoke($imgPath, $cropping = null)
    {
        $mediaModuleActive = Pi::service('module')->isActive('media') ? true : false;
        if ($mediaModuleActive) {
            $mediaQuality = (int)Pi::service('module')->config('image_quality', 'media');

            if ($mediaQuality) {
                $this->quality = $mediaQuality;
            }
        }

        $this->imgPath  = $imgPath;
        $this->imgParts = pathinfo($imgPath);
        $this->commands = '';
        $this->cropping = $cropping;

        return $this;
    }

    /**
     * Set custom module config
     * @param string $module
     * @return $this
     */
    public function setConfigModule($module)
    {
        if (is_string($module) && Pi::service('module')->isActive($module)) {
            $moduleConfig = Pi::service('registry')->config->read($module);

            if (isset($moduleConfig['image_quality'])) {
                $quality = (int)$moduleConfig['image_quality'];

                if ($quality) {
                    $this->quality = $quality;
                }
            }

            $defaultSizes = $this->getDefaultSizes();

            if (!empty($moduleConfig['image_largew']) && !empty($moduleConfig['image_largeh'])) {
                $defaultSizes['large'] = [
                    'width'  => $moduleConfig['image_largew'],
                    'height' => $moduleConfig['image_largeh'],
                ];
            }

            if (!empty($moduleConfig['image_itemw']) && !empty($moduleConfig['image_itemh'])) {
                $defaultSizes['item'] = [
                    'width'  => $moduleConfig['image_itemw'],
                    'height' => $moduleConfig['image_itemh'],
                ];
            }

            if (!empty($moduleConfig['image_mediumw']) && !empty($moduleConfig['image_mediumh'])) {
                $defaultSizes['medium'] = [
                    'width'  => $moduleConfig['image_mediumw'],
                    'height' => $moduleConfig['image_mediumh'],
                ];
            }

            if (!empty($moduleConfig['image_thumbw']) && !empty($moduleConfig['image_thumbh'])) {
                $defaultSizes['thumbnail'] = [
                    'width'  => $moduleConfig['image_thumbw'],
                    'height' => $moduleConfig['image_thumbh'],
                ];
            }

            $this->setDefaultSizes($defaultSizes);
        }

        return $this;
    }

    /**
     * Set default sizes
     * @param array $defaultSizes
     * @return $this
     */
    public function setDefaultSizes($defaultSizes)
    {
        $this->defaultSizes = $defaultSizes;

        return $this;
    }

    /**
     * Get default sizes
     * @param array $defaultSizes
     * @return array
     */
    public function getDefaultSizes()
    {
        return $this->defaultSizes;
    }

    /**
     * @param $widthOrSizeCode
     * @param $height
     * @return $this
     */
    public function thumb($widthOrSizeCode, $height = null)
    {
        if (is_string($widthOrSizeCode)) {
            $defaultSizes = $this->getDefaultSizes();
            $width        = $defaultSizes[$widthOrSizeCode]['width'];
            $height       = $defaultSizes[$widthOrSizeCode]['height'];
        } else {
            $width = $widthOrSizeCode;
        }

        $this->commands .= '$thumb,' . $width . ',' . $height;

        return $this;
    }

    /**
     * @param $widthOrSizeCode
     * @param $height
     * @return $this
     */
    public function thumbcrop($widthOrSizeCode, $height = null)
    {
        if (is_string($widthOrSizeCode)) {
            $defaultSizes = $this->getDefaultSizes();
            $width        = $defaultSizes[$widthOrSizeCode]['width'];
            $height       = $defaultSizes[$widthOrSizeCode]['height'];
        } else {
            $width = $widthOrSizeCode;
        }

        $this->commands .= '$thumbcrop,' . $width . ',' . $height;

        return $this;
    }

    /**
     * @param $widthOrSizeCode
     * @param $height
     * @return $this
     */
    public function resize($widthOrSizeCode, $height = null)
    {
        if (is_string($widthOrSizeCode)) {
            $defaultSizes = $this->getDefaultSizes();
            $width        = $defaultSizes[$widthOrSizeCode]['width'];
            $height       = $defaultSizes[$widthOrSizeCode]['height'];
        } else {
            $width = $widthOrSizeCode;
        }

        $this->commands .= '$resize,' . $width . ',' . $height;

        return $this;
    }

    /**
     * @param $value
     * @return $this
     */
    public function quality($value = null)
    {
        if ($value !== null && is_numeric($value)) {
            $this->commands .= '$quality,' . $value;
            $this->quality  = $value;
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

        if ($this->commands == '') {
            return \Pi::url($this->imgPath);
        }

        $options = [];

        $options['quality'] = $this->quality;

        $theme = Pi::service('theme')->current();
        $placeholderSource = Pi::service('asset')->getThemeAssetPath('image/placeholder.jpg', $theme, null, true);

        try {

            if (empty($this->imgParts['dirname'])) {
                throw new \Exception('No dirname found');
            }

            $file = ($this->imgParts['dirname'] && $this->imgParts['dirname'] !== '.' ? $this->imgParts['dirname'] . '/' : '') . $this->imgParts['filename'];

            if (!isset($this->imgParts['extension']) || $this->imgParts['extension'] == '') {
                throw new \Exception('No extension found');
            }

            $targetExtension = $this->imgParts['extension'];

            $source = ''
                . $file
                . '.' . $targetExtension;

            $placeholderSource = null;

            if (!file_exists($source)) {
                $source = null;
                $targetExtension = '404.' . $targetExtension;
            }

            $filenameCommand = str_replace(',','-', $this->commands); // remove separator parameters
            $filenameCommand = str_replace('$','', $filenameCommand); // remove separatir commands

            $filestring = str_replace('upload/media/original', '', $file);

            $target = 'upload/media/processed/'
                . $filenameCommand . '/'
                . $filestring
                . '.' . $targetExtension;

            if (!is_file($target)) {
                ini_set("memory_limit", "512M");

                $imagine         = new Imagine();
                $imageProcessing = new ImageProcessing($imagine);

                if ($source) {
                    $imageProcessing->process($source, $target, preg_replace('#^\$#', '', $this->commands), $this->cropping, $options);
                } else {
                    $imageProcessing->process($placeholderSource, $target, preg_replace('#^\$#', '', $this->commands));
                }
            }
        } catch (\Exception $e) {

            $source = null;
            $targetExtension = 'jpg';
            $filestring = 'placeholder';

            $filenameCommand = str_replace(',','-', $this->commands); // remove separator parameters
            $filenameCommand = str_replace('$','', $filenameCommand); // remove separatir commands

            $target = 'upload/media/processed/'
                . $filenameCommand . '/'
                . $targetExtension;

            $imagine         = new Imagine();
            $imageProcessing = new ImageProcessing($imagine);
            $imageProcessing->process($placeholderSource, $target, preg_replace('#^\$#', '', $this->commands));
        }

        $filepath = 'upload/media/processed/' . $filenameCommand . '/' . $filestring . '.' . $targetExtension;

        $finalUrl = \Pi::url($filepath);

        if($this->timestamp){
            $finalUrl .= '?' . $this->timestamp;
        }

        return $finalUrl;
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


    public function setTimestamp($timestamp){
        $this->timestamp = $timestamp;
    }
}
