<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
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
            'title'         => _a('Pi Engine features'),
            'description'   => _a('Introduction to Pi Engine.'),
            'type'          => 'html',
            'content'       => _a('WIDGET_PI_ENGINE_FEATURE'),
        );
        $result = Pi::api('block', 'system')->add($block);
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
            'name'          => 'highlights',
            'title'         => _a('Pi Highlights'),
            'description'   => _a('Introduction to Pi Engine with carousel.'),
            'type'          => 'carousel',
            'template'      => 'carousel/bootstrap',
        );
        $images = array(
            array(
                'caption'   => _a('Sustainable ecosystem'),
                'desc'      => _a('A sustainable ecosystem built upon open standard, open source code, open development and open management on Github.'),
                'link'      => 'http://pialog.org',
                'image'     => Pi::url('static/image/pi-ecosystem.png'),
            ),
            array(
                'caption'   => _a('Engineered development'),
                'desc'      => _a('Quality ensured engineering development with short learning curve, low skill requirements with clean MVC architecture, semantic templating, sophisticated API and strict starndards.'),
                'link'      => 'http://pialog.org',
                'image'     => Pi::url('static/image/pi-engineering.png'),
            ),
            array(
                'caption'   => _a('Visualization of application management'),
                'desc'      => _a('Easy and responsive application and content management based on visualized mangement tools and interface with page and widget mechanism.'),
                'link'      => 'http://pialog.org',
                'image'     => Pi::url('static/image/pi-visualization.png'),
            ),
            array(
                'caption'   => _a('Agile compliant development workflow'),
                'desc'      => _a('Role oriented architecture and deployment skeleton supports managable agile development workflow.'),
                'link'      => 'http://pialog.org',
                'image'     => Pi::url('static/image/pi-agile.png'),
            ),
        );
        $block['content'] = json_encode($images);

        $result = Pi::api('block', 'widget')->add($block);
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
