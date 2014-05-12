<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Module\Widget\Api;

use Pi;
use Pi\Application\Api\AbstractApi;
use Pi\Db\RowGateway\RowGateway;

/**
 * Block manipulation APIs
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Block extends AbstractApi
{
    /** @var string Module name */
    protected $module = 'widget';

    /**
     * Add a widget and derived block
     *
     * @param array  $block
     * @param string $type
     *
     * @return int
     */
    public function add(array $block, $type = '')
    {
        $id = 0;
        $result = $this->addBlock($block, $type);
        if ($result && !empty($result['root'])) {
            $widget = array(
                'block' => $result['root'],
                'name'  => $block['name'],
                'meta'  => $block['content'],
                'type'  => $type ?: $block['type'],
                'time'  => time(),
            );
            $row = Pi::model('widget', 'widget')->createRow($widget);
            $row->save();
            $id = (int) $row->id;
        }

        return $id;
    }

    /**
     * Add a block
     *
     * @param array  $block
     * @param string $type
     *
     * @return array
     */
    public function addBlock(array $block, $type = '')
    {
        $type = $type ?: $block['type'];
        $config = $this->getConfig($type);
        if ($config) {
            $block['config'] = $config;
        }
        $result = Pi::api('block', 'system')->add($block);

        return $result;
    }

    /**
     * Get config specs for a type of widget
     *
     * @param string $type
     *
     * @return array
     */
    public function getConfig($type = null)
    {
        $configs = array(
            'list' => array(
                'target_new'    => array(
                    'title'         => _a('Open link in new window'),
                    'edit'          => 'checkbox',
                    'filter'        => 'int',
                    'value'         => 0,
                ),
            ),
            'media' => array(
                'width'          => array(
                    'title'         => _a('Image width'),
                    'edit'          => 'text',
                    'filter'        => 'int',
                    'value'         => 150,
                ),
                'height'         => array(
                    'title'         => _a('Image height'),
                    'edit'          => 'text',
                    'filter'        => 'int',
                ),
                'target_new'    => array(
                    'title'         => _a('Open link in new window'),
                    'edit'          => 'checkbox',
                    'filter'        => 'int',
                    'value'         => 0,
                ),
            ),
            'spotlight' => array(
                'target_new'    => array(
                    'title'         => _a('Open link in new window'),
                    'edit'          => 'checkbox',
                    'filter'        => 'int',
                    'value'         => 0,
                ),
                'width'          => array(
                    'title'         => _a('Image width'),
                    'edit'          => 'text',
                    'filter'        => 'int',
                    'value'         => 400,
                ),
                'height'         => array(
                    'title'         => _a('Image height'),
                    'edit'          => 'text',
                    'filter'        => 'int',
                    'value'         => 300,
                ),
                'target_new'    => array(
                    'title'         => _a('Open link in new window'),
                    'edit'          => 'checkbox',
                    'filter'        => 'int',
                    'value'         => 0,
                ),
            ),
            'carousel' => array(
                'width'     => array(
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
                    'value'         => 4000,
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
                /*
                'two_col'    => array(
                    'title'         => _a('Two columns'),
                    'description'   => _a('Split image and text into two columns'),
                    'edit'          => 'checkbox',
                    'filter'        => 'int',
                    'value'         => 0,
                ),
                */
                'target_new'    => array(
                    'title'         => _a('New window'),
                    'description'   => _a('Open link in a new window'),
                    'edit'          => 'checkbox',
                    'filter'        => 'int',
                    'value'         => 0,
                )
            ),
        );

        if (null === $type) {
            $config = $configs;
        } elseif (isset($configs[$type])) {
            $config = $configs[$type];
        } else {
            $config = array();
        }

        return $config;
    }

    /**
     * Get specifications for template edit
     *
     * @param string $type
     *
     * @return array|bool
     */
    public function templateSpec($type = null)
    {
        $configs = array(
            'list'  => array(
                'type'  =>  'Module\Widget\Form\Element\ListTemplate',
            ),
            'media' => array(
                'type'  =>  'Module\Widget\Form\Element\MediaTemplate',
            ),
            'spotlight' => array(
                'type'  =>  'Module\Widget\Form\Element\SpotlightTemplate',
            ),
            'carousel'  => array(
                'type'  =>  'Module\Widget\Form\Element\CarouselTemplate',
            ),
            'html'      => false,
            'text'      => false,
            'markdown'  => false,
            'tab'       => false,
        );

        if (null === $type) {
            $config = $configs;
        } elseif (isset($configs[$type])) {
            $config = $configs[$type];
        } else {
            $config = array();
        }

        return $config;
    }
}
