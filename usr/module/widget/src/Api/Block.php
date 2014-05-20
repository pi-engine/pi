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
     * Add a block
     *
     * @param array  $block
     * @param string $type
     *
     * @return array
     */
    public function add(array $block, $type = '')
    {
        $type = $type ?: $block['type'];
        switch ($type) {
            case 'list':
                $block['config'] = array(
                    'width'          => array(
                        'title'         => _a('Image width'),
                        'edit'          => 'text',
                        'filter'        => 'int',
                    ),
                    'height'         => array(
                        'title'         => _a('Image height'),
                        'edit'          => 'text',
                        'filter'        => 'int',
                    ),
                );
                break;
            case 'carousel':
                $block['config'] = array(
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
                );
                break;
            default:
                break;
        }
        $result = Pi::api('block', 'system')->add($block);

        return $result;
    }
}
