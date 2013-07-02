<?php
/**
 * Pi module installer action
 *
 * You may not change or alter any portion of this comment or credits
 * of supporting developers from this source code or any supporting source code
 * which is considered copyrighted (c) material of the original comment or credit authors.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * @copyright       Copyright (c) Pi Engine http://www.xoopsengine.org
 * @license         http://www.xoopsengine.org/license New BSD License
 * @author          Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 * @package         Module\Widget
 * @subpackage      Installer
 */

namespace Module\Widget\Installer\Action;

use Pi;
use Pi\Application\Installer\Action\Install as BasicInstall;
use Pi\Application\Installer\Module as ModuleInstaller;
use Zend\EventManager\Event;

class Install extends BasicInstall
{
    /**
     * {@inheritDoc}
     */
    protected function attachDefaultListeners()
    {
        $events = $this->events;
        $events->attach('install.post', array($this, 'createBlock'));
        parent::attachDefaultListeners();
        return $this;
    }

    /**
     * Create blocks
     *
     * @param Event $e
     */
    public function createBlock(Event $e)
    {
        $model = Pi::model('widget', 'widget');

        /*
        // Feature block
        $block = array(
            'module'        => 'widget',
            'name'          => 'feature',
            'title'         => __('Pi Engine features'),
            'description'   => __('Introduction to Pi Engine.'),
            'type'          => 'html',
            'content'       => __('WIDGET_PI_ENGINE_FEATURE'),
        );
        $result = Pi::service('api')->system(array('block', 'add'), $block);
        $id = $result['root'];
        if ($id) {
            $widget = array(
                'block' => $id,
                'name'  => $block['name'],
                'meta'  => $block['content'],
                'type'  => $block['type'],
                'time'  => time(),
            );
            $row = $model->createRow($widget);
            $row->save();
        }
        */

        // Spotlight block
        $block = array(
            'module'        => 'widget',
            'name'          => 'spotlight',
            'title'         => __('Spotlight'),
            'description'   => __('Introduction to Pi Engine with carousel.'),
            'type'          => 'carousel',
            'template'      => 'carousel-bootstrap',
        );
        $block['config'] = array(
            'interval' => array(
                'title'         => __('Time interval (ms)'),
                'edit'          => 'text',
                'filter'        => 'number_int',
                'value'         => 2000,
            ),
            'pause' => array(
                'title'         => __('Mouse event to pause cycle'),
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
        $images = array(
            array(
                'caption'   => __('Pi Engine Architecture'),
                'desc'      => '',
                'link'      => 'http://pialog.org',
                'image'     => Pi::url('www/static/image/pi-architecture.png'),
            ),
            array(
                'caption'   => __('Pi Engine Theme'),
                'desc'      => '',
                'link'      => 'http://pialog.org',
                'image'     => Pi::url('www/static/image/pi-theme.png'),
            ),
            array(
                'caption'   => __('Pi Engine Skeleton'),
                'desc'      => '',
                'link'      => 'http://pialog.org',
                'image'     => Pi::url('www/static/image/pi-skeleton.png'),
            ),
            array(
                'caption'   => __('Pi Engine Ecosystem'),
                'desc'      => '',
                'link'      => 'http://pialog.org',
                'image'     => Pi::url('www/static/image/pi-ecosystem.png'),
            ),
        );
        $block['content'] = json_encode($images);

        $result = Pi::service('api')->system(array('block', 'add'), $block);
        $id = $result['root'];
        if ($id) {
            $widget = array(
                'block' => $id,
                'name'  => $block['name'],
                'meta'  => $block['content'],
                'type'  => $block['type'],
                'time'  => time(),
            );
            $row = $model->createRow($widget);
            $row->save();
        }

    }

}
