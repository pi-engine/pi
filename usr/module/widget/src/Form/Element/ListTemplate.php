<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
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
        $styles = array(
            $this->templateDir . '/title-summary'   => _a('Title and summary'),
            $this->templateDir . '/title-only'      => _a('Title only'),
            $this->templateDir . '/social-network'  => _a('Social network bar'),
        );
        $styles += $this->getList();

        return $styles;
    }
}
