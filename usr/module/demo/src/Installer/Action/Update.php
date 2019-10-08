<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 */

namespace Module\Demo\Installer\Action;

use Pi;
use Pi\Application\Installer\Action\Update as BasicUpdate;
use Zend\EventManager\Event;

class Update extends BasicUpdate
{
    protected function attachDefaultListeners()
    {
        $events = $this->events;
        $events->attach('install.post', [$this, 'postUpdate']);
        parent::attachDefaultListeners();

        return $this;
    }

    public function postUpdate(Event $e)
    {
        $model = Pi::model($module = $e->getParam('directory') . '/test');
        $data  = [
            'message' => sprintf(
                __('The module is updated on %s'),
                date('Y-m-d H:i:s')
            ),
        ];
        $model->insert($data);

        $this->setResult(
            'post-update',
            [
                'status'  => true,
                'message' => sprintf('Called from %s', __METHOD__),
            ]
        );
    }
}
