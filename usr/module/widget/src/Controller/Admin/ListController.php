<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Module\Widget\Controller\Admin;

use Pi;
use Module\Widget\Form\BlockListForm as BlockForm;

/**
 * For list block
 * 
 * @author Zongshu Lin <lin40553024@163.com>
 */
class ListController extends CarouselController
{
    /**
     * Widget type
     * @var string 
     */
    protected $type = 'list';

    /**
     * Get form instance
     * 
     * @return BlockForm
     */
    protected function getForm()
    {
        $this->form = $this->form ?: new BlockForm('block');

        return $this->form;
    }

    /**
     * Add a list block and default ACL rules
     */
    public function addAction()
    {
        parent::addAction();

        $this->view()->setTemplate('widget-list');
    }

    /**
     * Edit a carousel block
     */
    public function editAction()
    {
        parent::editAction();

        $this->view()->setTemplate('widget-list');
    }
    
    /**
     * {@inheritDoc}
     */
    protected function canonizeContent($content)
    {
        $content = json_decode($content, true);
        $items = array();
        foreach ($content as $item) {
            if ($item['image']) {
                if (!$this->isAbsoluteUrl($item['image'])) {
                    $item['image'] = $this->urlRoot() . '/' . $item['image'];
                }
            } else {
                $item['image'] = '';
            }
            $items[] = $item;
        }

        return json_encode($items);
    }
}
