<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Module\Widget\Form\Element;

use Pi;
use Zend\Form\Element\Select;

class CarouselTemplate extends Select
{
    protected function getStyles()
    {
        $styles = array(
            'carousel/bootstrap'    => __('Bootstrap slide') . ' (bootstrap)',
            'carousel/jcarousel'    => __('jCarousel riding Carousel') . ' (jcarousel)',
            'carousel/parallax'     => __('Parallax Content Slider') . ' (parallax)',
        );
        // Load custom templates
        $customPath = sprintf(
            '%s/module/widget/template/block/carousel',
            Pi::path('custom')
        );
        $iterator = new \DirectoryIterator($customPath);
        foreach ($iterator as $fileinfo) {
            if (!$fileinfo->isFile()) {
                continue;
            }
            $filename = $fileinfo->getFilename();
            $extension = pathinfo($filename, PATHINFO_EXTENSION);
            if ('phtml' != $extension) {
                continue;
            }
            $name = pathinfo($filename, PATHINFO_FILENAME);
            if (preg_match('/[^a-z0-9_\-]/', $name)) {
                continue;
            }
            $styles['carousel/' . $name] = __('Custom: ') . $name;
        }

        return $styles;
    }

    /**
     * @return array
     */
    public function getValueOptions()
    {
        if (empty($this->valueOptions)) {
            $this->valueOptions = $this->getStyles();
        }

        return $this->valueOptions;
    }
}
