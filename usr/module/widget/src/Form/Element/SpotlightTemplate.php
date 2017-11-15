<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 */

namespace Module\Widget\Form\Element;

class SpotlightTemplate extends MediaTemplate
{
    /**
     * {@inheritDoc}
     */
    protected $templateDir = 'spotlight';

    /**
     * {@inheritDoc}
     */
    protected function getStyles()
    {
        $styles = array(
            $this->templateDir . '/spot-top'    =>  _a('Spot on top'),
            $this->templateDir . '/spot-left'   =>  _a('Spot on left'),
        );
        $styles += $this->getList();

        return $styles;
    }
}
