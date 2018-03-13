<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 */

namespace Module\Widget\Form\Element;

class ListTemplate extends MediaTemplate
{
    /**
     * {@inheritDoc}
     */
    protected $templateDir = 'list';

    /**
     * {@inheritDoc}
     */
    protected function getStyles()
    {
        $styles = [
            $this->templateDir . '/title-summary'  => _a('Title and summary'),
            $this->templateDir . '/title-only'     => _a('Title only'),
            $this->templateDir . '/social-network' => _a('Social network bar'),
        ];
        $styles += $this->getList();

        return $styles;
    }
}
