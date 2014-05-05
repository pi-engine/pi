<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Module\Widget\Controller\Admin;

use Module\Widget\Form\BlockSpotlightForm as BlockForm;

/**
 * For spotlight block
 */
class SpotlightController extends MediaController
{
    protected $type = 'spotlight';

    protected function getForm()
    {
        $this->form = $this->form ?: new BlockForm('block');

        return $this->form;
    }

    /**
     * Add a spotlight block and default ACL rules
     */
    public function addAction()
    {
        parent::addAction();

        $this->view()->setTemplate('widget-spotlight');
    }

    /**
     * Edit a spotlight block
     */
    public function editAction()
    {
        parent::editAction();

        $this->view()->setTemplate('widget-spotlight');
    }
}
