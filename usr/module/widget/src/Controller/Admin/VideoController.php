<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Module\Widget\Controller\Admin;

/**
 * For spotlight block
 */
class VideoController extends MediaController
{
    /**
     * {@inheritDoc}
     */
    protected $type = 'video';

    /**
     * {@inheritDoc}
     */
    protected $editTemplate = 'widget-video';

    /**
     * {@inheritDoc}
     */
    protected $formClass = 'BlockVideoForm';
}
