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
use Pi\View\Helper\Block as RenderHelper;

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
                'meta'  => isset($block['meta'])
                        ? $block['meta']
                        : $block['content'],
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
        if (is_array($block['content'])) {
            $block['content'] = json_encode($block['content']);
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
        $config = Pi::config('', 'widget');
        $configs = array(
            'list' => array(
                'target_new'    => array(
                    'title'         => _a('Open link in new window'),
                    'edit'          => 'checkbox',
                    'filter'        => 'int',
                    'value'         => $config['target_new'],
                ),
            ),
            'media' => array(
                'width'          => array(
                    'title'         => _a('Image width'),
                    'edit'          => 'text',
                    'filter'        => 'int',
                    'value'         => $config['image_width_media'],
                ),
                'height'         => array(
                    'title'         => _a('Image height'),
                    'edit'          => 'text',
                    'filter'        => 'int',
                    'value'         => $config['image_height_media'],
                ),
                'target_new'    => array(
                    'title'         => _a('Open link in new window'),
                    'edit'          => 'checkbox',
                    'filter'        => 'int',
                    'value'         => $config['target_new'],
                ),
            ),
            'spotlight' => array(
                'width'          => array(
                    'title'         => _a('Image width'),
                    'edit'          => 'text',
                    'filter'        => 'int',
                    'value'         => $config['image_width_spotlight'],
                ),
                'height'         => array(
                    'title'         => _a('Image height'),
                    'edit'          => 'text',
                    'filter'        => 'int',
                    'value'         => $config['image_height_spotlight'],
                ),
                'target_new'    => array(
                    'title'         => _a('Open link in new window'),
                    'edit'          => 'checkbox',
                    'filter'        => 'int',
                    'value'         => $config['target_new'],
                ),
            ),
            'carousel' => array(
                'width'     => array(
                    'title'         => _a('Image width'),
                    'edit'          => 'text',
                    'filter'        => 'int',
                    'value'         => $config['image_width_carousel'],
                ),
                'height'    => array(
                    'title'         => _a('Image height'),
                    'edit'          => 'text',
                    'filter'        => 'int',
                    'value'         => $config['image_height_carousel'],
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
                    'value'         => $config['target_new'],
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

    /**
     * Render a widget
     *
     * @param RenderHelper  $helper
     * @param array         $block
     * @param array         $options
     *
     * @return array|string
     */
    public function render(
        RenderHelper $helper,
        array $block,
        array $options = array()
    ) {
        $transliterateGlobals = function($content) {
            $globalsMap = array(
                'sitename'  => Pi::config('sitename'),
                'slogan'    => Pi::config('slogan'),
                'siteurl'   => Pi::url('www'),
            );
            foreach ($globalsMap as $var => $val) {
                $content = str_replace('%' . $var . '%', $val, $content);
            }

            return $content;
        };

        switch ($block['type']) {
            // Scripting widgets
            case 'script':
                $result = call_user_func($block['render'], $options);
                break;

            // spotlight
            case 'spotlight':
                // list group
            case 'list':
                // media object
            case 'media':
                // carousel
            case 'carousel':
                $items = empty($block['content'])
                    ? false : json_decode($block['content'], true);
                if ($items && is_array($items)) {
                    $result = array(
                        'items'     => $items,
                        'options'   => $options,
                    );
                } else {
                    $result = array();
                }
                break;

            // compound tab
            case 'tab':
                $result = array();
                $list = json_decode($block['content'], true);
                foreach ($list as $tab) {
                    $entity = isset($tab['name']) ? $tab['name'] : intval($tab['id']);
                    $row = Pi::model('block')->find($entity);
                    if (!$row || !$row->active) {
                        continue;
                    }
                    $data = $helper->renderBlock($row);
                    if (empty($data['content'])) {
                        continue;
                    }
                    $result[] = array(
                        'caption'   => !empty($tab['caption'])
                                ? $tab['caption'] : $data['title'],
                        'link'      => !empty($tab['link']) ? $tab['link'] : '',
                        'content'   => $data['content'],
                    );
                }
                break;

            // static HTML
            case 'html':
                $result = Pi::service('markup')->render(
                    $block['content'],
                    'html'
                );
                $result = $transliterateGlobals($result);
                break;
            // static markdown
            case 'markdown':
                $result = Pi::service('markup')->render(
                    $block['content'],
                    'html',
                    'markdown'
                );
                $result = $transliterateGlobals($result);
                break;
            // static text
            case 'text':
            default:
                $result = Pi::service('markup')->render(
                    $block['content'],
                    'text'
                );
                $result = $transliterateGlobals($result);
                break;
        }

        return $result;
    }
}
