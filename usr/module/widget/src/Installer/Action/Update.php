<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace   Module\Widget\Installer\Action;

use Pi;
use Pi\Application\Installer\Action\Update as BasicUpdate;
use Module\User\Installer\Schema;
use Zend\EventManager\Event;

/**
 * Module update handler
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Update extends BasicUpdate
{
    /**
     * {@inheritDoc}
     */
    protected function attachDefaultListeners()
    {
        $events = $this->events;
        $events->attach('update.post', array($this, 'updateCarousel'));
        parent::attachDefaultListeners();

        return $this;
    }

    /**
     * Update carousel blocks
     *
     * @param Event $e
     * @return bool
     */
    public function updateCarousel(Event $e)
    {
        $version = $e->getParam('version');
        if (version_compare($version, '1.1.0', '>')) {
            return true;
        }

        $rowset = Pi::model('block_root')->select(array('type' => 'carousel'));
        foreach ($rowset as $row) {
            $row->config = array(
                'height'    => array(
                    'title'         => _a('Block frame height'),
                    'edit'          => 'text',
                    'filter'        => 'number_int',
                ),
                'interval' => array(
                    'title'         => _a('Time interval (ms)'),
                    'edit'          => 'text',
                    'filter'        => 'number_int',
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
            $row->save();
        }

        return true;
    }
}
