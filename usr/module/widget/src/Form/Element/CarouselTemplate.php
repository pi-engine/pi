<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Module\Widget\Form\Element;

class CarouselTemplate extends MediaTemplate
{
    /**
     * {@inheritDoc}
     */
    protected $templateDir = 'carousel';

    /**
     * {@inheritDoc}
     */
    protected function getStyles()
    {
        $styles = array(
            $this->templateDir . '/bootstrap'           =>  _a('Bootstrap slide') . ' (bootstrap)',
            $this->templateDir . '/bootstrap-twocol'    =>  _a('Bootstrap two columns') . ' (bootstrap-twocol)',
            $this->templateDir . '/jcarousel'           =>  _a('jQuery riding Carousel') . ' (jcarousel)',
            $this->templateDir . '/parallax'            =>  _a('Parallax Content Slider') . ' (parallax)',
        );
        $styles += $this->getList();

        return $styles;
    }
}
