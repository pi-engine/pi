<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
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
        $styles = [
            $this->templateDir . '/bootstrap'        => _a('Bootstrap slide') . ' (bootstrap)',
            $this->templateDir . '/bootstrap-twocol' => _a('Bootstrap two columns') . ' (bootstrap-twocol)',
            $this->templateDir . '/owl-carousel'     => _a('Owl Carousel 2') . ' (owl-carousel)',
            $this->templateDir . '/parallax'         => _a('Parallax Content Slider') . ' (parallax)',
        ];
        $styles += $this->getList();

        return $styles;
    }
}
