<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 */

namespace Module\Widget\Form\Element;

class VideoTemplate extends MediaTemplate
{
    /**
     * {@inheritDoc}
     */
    protected $templateDir = 'video';

    /**
     * {@inheritDoc}
     */
    protected function getStyles()
    {
        $styles = [
            $this->templateDir . '/video-only'    => _a('Player only'),
            $this->templateDir . '/video-summary' => _a('Player and summary'),
        ];
        $styles += $this->getList();

        return $styles;
    }
}
