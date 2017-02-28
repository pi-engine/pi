<?php
/**
 * Smart image resizing (and manipulation) by url module for Zend Framework 2
 *
 * @link      http://github.com/tck/zf2-imageresizer for the canonical source repository
 * @copyright Copyright (c) 2014 Tobias Knab
 * 
 * For the full copyright and license information, please view the LICENSE.txt
 * file that was distributed with this source code.
 */

namespace Pi\Application\Service;

use Imagine\Image\AbstractImagine;
use Imagine\Image\ImageInterface;
use Imagine\Image\Box;
use Imagine\Image\Point;
use Imagine\Image\Palette\RGB;
use BadMethodCallException;

/**
 * Image Processing
 * 
 * @package TckImageResizer
 */
class ImageProcessing
{
    /**
     * @var AbstractImagine
     */
    protected $imagine;
    
    /**
     * @var ImageInterface
     */
    protected $image;
    
    
    /**
     * constructor
     * 
     * @param AbstractImagine
     */
    public function __construct(AbstractImagine $imagine)
    {
        $this->setImagineService($imagine);
    }
    
    /**
     * set the imagine service
     *
     * @param AbstractImagine $imagine
     * @return $this
     */
    public function setImagineService(AbstractImagine $imagine)
    {
        $this->imagine = $imagine;

        return $this;
    }
    
    /**
     * Get the imagine service
     * 
     * @return AbstractImagine
     */
    public function getImagineService()
    {
        return $this->imagine;
    }
    
    /**
     * Process command to image source and save to target
     * 
     * @param string $source
     * @param string $target
     * @param string $commands
     * 
     * @return void
     */
    public function process($source, $target, $commands)
    {
        $targetFolder = pathinfo($target, PATHINFO_DIRNAME);
        if (!file_exists($targetFolder)) {
            mkdir($targetFolder, 0777, true);
        }
        $this->image = $this->getImagineService()->open($source);
        foreach ($this->analyseCommands($commands) as $command) {
            if ($this->runCommand($command)) {
                continue;
            }
            $this->runCustomCommand($command);
        }
        
        $this->image->save($target);
    }
    
    /**
     * Process command to create 404 image and save to target
     * 
     * @param string $target
     * @param string $commands
     * 
     * @return void
     */
    public function process404($target, $commands)
    {
        if (file_exists($target)) {
            return;
        }
        
        $targetFolder = pathinfo($target, PATHINFO_DIRNAME);
        if (!file_exists($targetFolder)) {
            mkdir($targetFolder, 0777, true);
        }
        
        $text = 'Not found';
        $backgroundColor = null;
        $color = null;
        $width = null;
        $height = null;
        foreach ($this->analyseCommands($commands) as $command) {
            if ('thumb' === $command['command'] || 'resize' === $command['command']) {
                $width = $command['params'][0];
                $height = $command['params'][1];
                
            } elseif ('404' === $command['command']) {
                if (isset($command['params'][0]) && self::valid($command['params'][0])) {
                    $text = self::decode($command['params'][0]);
                }
                if (isset($command['params'][1])) {
                    $backgroundColor = $command['params'][1];
                }
                if (isset($command['params'][2])) {
                    $color = $command['params'][2];
                }
                if (isset($command['params'][3])) {
                    $width = $command['params'][3];
                }
                if (isset($command['params'][4])) {
                    $height = $command['params'][4];
                }
            }
        }
        $this->image404($text, $backgroundColor, $color, $width, $height);
        
        $this->image->save($target);
    }
    
    /**
     * Analyse commands string and returns array with command/params keys
     * 
     * @param string $commands
     * 
     * @return array
     */
    protected function analyseCommands($commands)
    {
        $commandList = array();
        foreach (explode('$', $commands) as $commandLine) {
            $params = explode(',', $commandLine);
            $command = array_shift($params);
            $commandList[] = array(
                'command' => $command,
                'params' => $params,
            );
        }
        return $commandList;
    }
    
    /**
     * Run command if exists
     * 
     * @param array $command
     * 
     * @return boolean
     */
    protected function runCommand($command)
    {
        $method = 'image' . ucfirst(strtolower($command['command']));
        if (!method_exists($this, $method)) {
            return false;
        }
        call_user_func_array(array($this, $method), $command['params']);
        
        return true;
    }
    
    /**
     * Run custom command if exists
     * 
     * @param array $command
     * 
     * @return boolean
     */
    protected function runCustomCommand($command)
    {
        if (!CommandRegistry::hasCommand($command['command'])) {
            throw new BadMethodCallException('Command "' . $command['command'] . '" not found');
        }
        $customCommand = CommandRegistry::getCommand($command['command']);
        
        array_unshift($command['params'], $this->image);
        call_user_func_array($customCommand, $command['params']);
        
        return true;
    }
    
    /**
     * Command image thumb
     * 
     * @param int $width
     * @param int $height
     * 
     * @return void
     */
    protected function imageThumb($width, $height)
    {
        $width = (int) $width;
        $height = (int) $height;
        if ($width <= 0) {
            throw new BadMethodCallException('Invalid parameter width for command "thumb"');
        }
        if ($height <= 0) {
            throw new BadMethodCallException('Invalid parameter height for command "thumb"');
        }
        $this->image = $this->image->thumbnail(new Box($width, $height));
    }

    /**
     * Command image thumb crop
     * @param $width
     * @param $height
     */
    protected function imageThumbcrop($width, $height)
    {
        $width = (int) $width;
        $height = (int) $height;
        if ($width <= 0) {
            throw new BadMethodCallException('Invalid parameter width for command "thumb"');
        }
        if ($height <= 0) {
            throw new BadMethodCallException('Invalid parameter height for command "thumb"');
        }
        $this->image = $this->image->thumbnail(new Box($width, $height), ImageInterface::THUMBNAIL_OUTBOUND);
    }
    
    /**
     * Command image resize
     * 
     * @param int $width
     * @param int $height
     * 
     * @return void
     */
    protected function imageResize($width, $height)
    {
        $width = (int) $width;
        $height = (int) $height;
        if ($width <= 0) {
            throw new BadMethodCallException('Invalid parameter width for command "resize"');
        }
        if ($height <= 0) {
            throw new BadMethodCallException('Invalid parameter height for command "resize"');
        }
        $this->image->resize(new Box($width, $height));
    }
    
    /**
     * Command image grayscale
     * 
     * @return void
     */
    protected function imageGrayscale()
    {
        $this->image->effects()->grayscale();
    }
    
    /**
     * Command image negative
     * 
     * @return void
     */
    protected function imageNegative()
    {
        $this->image->effects()->negative();
    }
    
    /**
     * Command image gamma
     * 
     * @param float $correction
     * 
     * @return void
     */
    protected function imageGamma($correction)
    {
        $correction = (float) $correction;
        
        $this->image->effects()->gamma($correction);
    }
    
    /**
     * Command image colorize
     * 
     * @param string $hexColor
     * 
     * @return void
     */
    protected function imageColorize($hexColor)
    {
        if (strlen($hexColor) != 6 || !preg_match('![0-9abcdef]!i', $hexColor)) {
            throw new BadMethodCallException('Invalid parameter color for command "colorize"');
        }
        $color = $this->image->palette()->color('#' . $hexColor);
        
        $this->image->effects()->colorize($color);
    }
    
    /**
     * Command image sharpen
     * 
     * @return void
     */
    protected function imageSharpen()
    {
        $this->image->effects()->sharpen();
    }
    
    /**
     * Command image blur
     * 
     * @param float $sigma
     * 
     * @return void
     */
    protected function imageBlur($sigma = 1)
    {
        $sigma = (float) $sigma;
        
        $this->image->effects()->blur($sigma);
    }
    
    /**
     * Command image version
     * 
     * @return void
     */
    protected function imageVersion()
    {
    }
    
    /**
     * Command image resize
     * 
     * @param string $text
     * @param string $backgroundColor
     * @param string $color
     * @param int $width
     * @param int $height
     * 
     * @return void
     */
    protected function image404($text, $backgroundColor, $color, $width, $height)
    {
        $text = (string) $text;
        $backgroundColor = (string) $backgroundColor;
        $color = (string) $color;
        $width = (int) $width;
        $height = (int) $height;
        
        if (strlen($backgroundColor) != 6 || !preg_match('![0-9abcdef]!i', $backgroundColor)) {
            $backgroundColor = 'F8F8F8';
        }
        if (strlen($color) != 6 || !preg_match('![0-9abcdef]!i', $color)) {
            $color = '777777';
        }
        if ($width <= 0) {
            $width = 100;
        }
        if ($height <= 0) {
            $height = 100;
        }
        
        $palette = new RGB();
        $size  = new Box($width, $height);
        $this->image = $this->getImagineService()->create($size, $palette->color('#' . $backgroundColor, 0));

        if ($text) {
            $this->drawCenteredText($text, $color);
        }
    }
    
    /**
     * Draw centered text in current image
     * 
     * @param string $text
     * @param string $color
     * 
     * @return void
     */
    protected function drawCenteredText($text, $color)
    {
        $width = $this->image->getSize()->getWidth();
        $height = $this->image->getSize()->getHeight();
        $fontColor = $this->image->palette()->color('#' . $color);
        $fontSize = 48;
        $widthFactor = $width > 160 ? 0.8 : 0.9;
        $heightFactor = $height > 160 ? 0.8 : 0.9;
        do {
            $font = $this->getImagineService()
              ->font(__DIR__ . '/../../../../www/static/font/Roboto-Regular.ttf', $fontSize, $fontColor);
            $fontBox = $font->box($text);
            $fontSize = round($fontSize * 0.8);

        } while ($fontSize > 5
          && ($width * $widthFactor < $fontBox->getWidth() || $height * $heightFactor < $fontBox->getHeight()));

        $pointX = max(0, floor(($width - $fontBox->getWidth()) / 2));
        $pointY = max(0, floor(($height - $fontBox->getHeight()) / 2));

        $this->image->draw()->text($text, $font, new Point($pointX, $pointY));
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

    /**
     * base64 decode
     *
     * @param string $data data to decode
     * @return string decoded data
     */
    public static function decode($data)
    {
        $rData = strtr($data, '-_', '+/');
        $rMod4 = strlen($rData) % 4;
        if ($rMod4) {
            $rData .= substr('====', $rMod4);
        }
        return base64_decode($rData);
    }

    /**
     * base64 check string
     *
     * @param string $data data to check
     * @return boolean vaild or not
     */
    public static function valid($data)
    {
        return (boolean) preg_match('!^[a-zA-Z0-9\-_]*$!', $data);
    }
}
