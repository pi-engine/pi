<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Module\Demo\Installer\Action;

use Pi;
use Pi\Application\Installer\Action\Install as BasicInstall;
use Zend\EventManager\Event;

class Install extends BasicInstall
{
    protected function attachDefaultListeners()
    {
        $events = $this->events;
        $events->attach('install.pre', array($this, 'preInstall'), 1000);
        $events->attach('install.post', array($this, 'postInstall'), 1);
        parent::attachDefaultListeners();

        return $this;
    }

    public function preInstall(Event $e)
    {
        $this->setResult('pre-install', array(
            'status'    => true,
            'message'   => sprintf('Called from %s', __METHOD__),
        ));
    }

    public function postInstall(Event $e)
    {
        $model = Pi::model($this->module . '/test');
        $data = array(
            'message'   => sprintf(
                __('The module is installed on %s'),
                date('Y-m-d H:i:s')
            ),
        );
        $model->insert($data);

        $model = Pi::model($this->module . '/page');
        $flag = 0;
        for ($page = 1; $page <= 100; $page++) {
            $model->insert(array(
                'title' => sprintf('Page #%d', $page),
                'flag'  => $flag++ % 2,
            ));
        }

        $this->setResult('post-install', array(
            'status'    => true,
            'message'   => sprintf('Called from %s', __METHOD__),
        ));
    }
}
