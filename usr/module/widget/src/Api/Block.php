<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
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
            case 'carousel':
                $block['config'] = array(
                    'height'    => array(
                        'title'         => _a('Block frame height'),
                        'edit'          => 'text',
                        'filter'        => 'number_int',
                    ),
                    'interval' => array(
                        'title'         => _a('Time interval (ms)'),
                        'edit'          => 'text',
                        'filter'        => 'number_int',
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
