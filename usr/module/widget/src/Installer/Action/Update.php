<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
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
        $events->attach('update.post', array($this, 'updateBlock'));
        parent::attachDefaultListeners();

        return $this;
    }

    /**
     * Update block config specs and content meta
     *
     * @param Event $e
     * @return bool
     */
    public function updateBlock(Event $e)
    {
        $version = $e->getParam('version');
        if (version_compare($version, '2.0.0', '>=')) {
            return true;
        }

        $rowset = Pi::model('block_root')->select(array(
            'module'    => 'widget',
            'type <> ?' => 'script',
        ));
        foreach ($rowset as $row) {
            $type = $row->type ?: '';
            $row->config = Pi::api('block', 'widget')->getConfig($type);
            $row->save();
        }

        $update = array(
            'content'   => Pi::db()->expression(sprintf(
                    'REPLACE(content, %s, %s)',
                    '\'","desc":"\'',
                    '\'","summary":"\''
                )),
        );
        $where = array(
            'module'    => 'widget',
            'type'      => array('list', 'media', 'carousel'),
        );
        Pi::model('block')->update($update, $where);

        $update = array(
            'meta'   => Pi::db()->expression(sprintf(
                    'REPLACE(meta, %s, %s)',
                    '\'","desc":"\'',
                    '\'","summary":"\''
                )),
        );
        $where = array(
            'type'      => array('list', 'media', 'carousel'),
        );
        Pi::model('widget', 'widget')->update($update, $where);

        return true;
    }
}
