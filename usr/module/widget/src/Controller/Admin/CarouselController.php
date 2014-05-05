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
use Module\Widget\Form\BlockCarouselForm as BlockForm;

/**
 * For carousel block
 */
class CarouselController extends MediaController
{
    protected $type = 'carousel';

    protected function getForm()
    {
        $this->form = $this->form ?: new BlockForm('block');

        return $this->form;
    }

    /**
     * Add a carousel block and default ACL rules
     */
    public function addAction()
    {
        parent::addAction();

        $this->view()->setTemplate('widget-carousel');
    }

    /**
     * Edit a carousel block
     */
    public function editAction()
    {
        parent::editAction();

        $this->view()->setTemplate('widget-carousel');
    }

    protected function canonizePost($values)
    {
        $values = parent::canonizePost($values);
        /**/
        // Set block configs
        if (empty($values['id'])) {
            $values['config'] = array(
                'width'    => array(
                    'title'         => _a('Image width'),
                    'edit'          => 'text',
                    'filter'        => 'int',
                ),
                'height'    => array(
                    'title'         => _a('Image height'),
                    'edit'          => 'text',
                    'filter'        => 'int',
                ),
                'interval' => array(
                    'title'         => _a('Time interval (ms)'),
                    'edit'          => 'text',
                    'filter'        => 'int',
                    'value'         => 2000,
                ),
                'pause' => array(
                    'title'         => _a('Mouse event'),
                    'description'   => _a('Event to pause cycle'),
                    'edit'          => array(
                        'type'  =>  'select',
                        'options'   => array(
                            'options'   => array(
                                'hover' => 'hover',
                            ),
                        ),
                    ),
                    'value'         => 'hover',
                ),
            );
        }
        /**/

        return $values;
    }
}
