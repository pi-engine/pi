<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Module\Widget\Form\Element;

use Pi;
use Zend\Form\Element\Select;

class CarouselTemplate extends Select
{
    protected function getStyles()
    {
        $styles = array(
            'carousel-bootstrap'    => __('Bootstrap slide'),
            'carousel-jcarousel'    => __('jCarousel riding Carousel'),
            'carousel-parallax'     => __('Parallax Content Slider'),
        );

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
