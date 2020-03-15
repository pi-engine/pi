<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
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
     * @param array $block
     * @param string $type
     *
     * @return int
     */
    public function add(array $block, $type = '')
    {
        $id     = 0;
        $result = $this->addBlock($block, $type);
        if ($result && !empty($result['root'])) {
            $widget = [
                'block' => $result['root'],
                'name'  => $block['name'],
                'meta'  => isset($block['meta'])
                    ? $block['meta']
                    : $block['content'],
                'type'  => $type ?: $block['type'],
                'time'  => time(),
            ];
            $row    = Pi::model('widget', 'widget')->createRow($widget);
            $row->save();
            $id = (int)$row->id;
        }

        return $id;
    }

    /**
     * Add a block
     *
     * @param array $block
     * @param string $type
     *
     * @return array
     */
    public function addBlock(array $block, $type = '')
    {
        $type   = $type ?: $block['type'];
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
        $config      = Pi::config('', 'widget');
        $imgWidth    = function ($value) {
            return [
                'title'  => _a('Image width'),
                'edit'   => 'text',
                'filter' => 'int',
                'value'  => $value,
            ];
        };
        $imgHeight   = function ($value) {
            return [
                'title'  => _a('Image height'),
                'edit'   => 'text',
                'filter' => 'int',
                'value'  => $value,
            ];
        };
        $maxRows     = [
            'title'  => _a('Max rows to display'),
            'edit'   => [
                'type'    => 'select',
                'options' => [
                    'options' => [
                        0  => _a('No limit'),
                        1  => _a('1 row'),
                        2  => sprintf(_a('%d rows'), 2),
                        3  => sprintf(_a('%d rows'), 3),
                        4  => sprintf(_a('%d rows'), 4),
                        6  => sprintf(_a('%d rows'), 6),
                        12 => sprintf(_a('%d rows'), 12),
                    ],
                ],
            ],
            'filter' => 'int',
        ];
        $targetNew   = [
            'title'  => _a('Open link in new window'),
            'edit'   => 'checkbox',
            'filter' => 'int',
            'value'  => $config['target_new'],
        ];
        $circleImage = [
            'title'  => _a('Circle image'),
            'edit'   => 'checkbox',
            'filter' => 'int',
            'value'  => $config['circle_image'],
        ];
        $configs     = [
            'list'      => [
                'max_rows'   => $maxRows,
                'target_new' => $targetNew,
            ],
            'media'     => [
                'width'        => $imgWidth($config['image_width_media']),
                'height'       => $imgHeight($config['image_height_media']),
                'max_rows'     => $maxRows,
                'target_new'   => $targetNew,
                'circle_image' => $circleImage,
            ],
            'spotlight' => [
                'width'      => $imgWidth($config['image_width_spotlight']),
                'height'     => $imgHeight($config['image_height_spotlight']),
                'max_rows'   => $maxRows,
                'target_new' => $targetNew,
            ],
            'carousel'  => [
                'width'      => $imgWidth($config['image_width_carousel']),
                'height'     => $imgHeight($config['image_height_carousel']),
                'interval'   => [
                    'title'  => _a('Time interval (ms)'),
                    'edit'   => 'text',
                    'filter' => 'int',
                    'value'  => 4000,
                ],
                'pause'      => [
                    'title'       => _a('Mouse event'),
                    'description' => _a('Event to pause cycle'),
                    'edit'        => [
                        'type'    => 'select',
                        'options' => [
                            'options' => [
                                'hover' => 'hover',
                            ],
                        ],
                    ],
                    'value'       => 'hover',
                ],
                'max_rows'   => $maxRows,
                'target_new' => $targetNew,
            ],
            'video' => [
                'hls_mime_type' => [
                    'title'  => _a('Mime type for HLS video'),
                    'edit'   => 'text',
                    'value'  => 'application/x-mpegURL',
                ],
            ],
        ];

        if (null === $type) {
            $config = $configs;
        } elseif (isset($configs[$type])) {
            $config = $configs[$type];
        } else {
            $config = [];
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
        $configs = [
            'list'      => [
                'type' => 'Module\Widget\Form\Element\ListTemplate',
            ],
            'media'     => [
                'type' => 'Module\Widget\Form\Element\MediaTemplate',
            ],
            'spotlight' => [
                'type' => 'Module\Widget\Form\Element\SpotlightTemplate',
            ],
            'carousel'  => [
                'type' => 'Module\Widget\Form\Element\CarouselTemplate',
            ],
            'video'     => [
                'type' => 'Module\Widget\Form\Element\VideoTemplate',
            ],
            'html'      => false,
            'text'      => false,
            'markdown'  => false,
            'tab'       => false,
        ];

        if (null === $type) {
            $config = $configs;
        } elseif (isset($configs[$type])) {
            $config = $configs[$type];
        } else {
            $config = [];
        }

        return $config;
    }

    /**
     * Render a widget
     *
     * @param RenderHelper $helper
     * @param array $block
     * @param array $options
     *
     * @return array|string
     */
    public function render(
        RenderHelper $helper,
        array $block,
        array $options = []
    )
    {
        $transliterateGlobals = function ($content) {
            $globalsMap = [
                'sitename' => Pi::config('sitename'),
                'slogan'   => Pi::config('slogan'),
                'siteurl'  => Pi::url('www'),
            ];
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
                // video player
            case 'video':
                // carousel
            case 'carousel':
                $items = empty($block['content'])
                    ? false : json_decode($block['content'], true);
                if ($items && is_array($items)) {
                    $result = [
                        'items'   => $items,
                        'options' => $options,
                    ];
                } else {
                    $result = [];
                }
                break;

            // compound tab
            case 'tab':
                $result = [];
                $list   = json_decode($block['content'], true);
                foreach ($list as $tab) {
                    $entity = isset($tab['name']) ? $tab['name'] : intval($tab['id']);
                    $row    = Pi::model('block')->find($entity);
                    if (!$row || !$row->active) {
                        continue;
                    }
                    $data = $helper->renderBlock($row);
                    if (empty($data['content'])) {
                        continue;
                    }
                    $result[] = [
                        'caption' => !empty($tab['caption']) ? $tab['caption'] : $data['title'],
                        'link'    => !empty($tab['link']) ? $tab['link'] : '',
                        'content' => $data['content'],
                    ];
                }
                break;

            // static HTML
            case 'html':
                $result = Pi::service('markup')->compile(
                    $block['content'],
                    'html'
                );
                $result = $transliterateGlobals($result);
                break;
            // static markdown
            case 'markdown':
                $result = Pi::service('markup')->compile(
                    $block['content'],
                    'markdown'
                );
                $result = $transliterateGlobals($result);
                break;
            // static text
            case 'text':
            default:
                $result = Pi::service('markup')->compile(
                    $block['content'],
                    'text'
                );
                $result = $transliterateGlobals($result);
                break;
        }

        return $result;
    }
}