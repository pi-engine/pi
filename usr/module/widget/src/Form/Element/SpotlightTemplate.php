<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
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
            $this->templateDir . '/top-bottom'  =>  _a('Top-bottom layout'),
            $this->templateDir . '/left-right'  =>  _a('Left-right layout'),
        );
        $styles += $this->getList();

        return $styles;
    }
}
