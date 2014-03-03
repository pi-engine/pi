<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Module\User\Field;

/**
 * Social tool handler
 *
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Tool extends CustomCompoundHandler
{
    /** @var string Field name and table name */
    protected $name = 'tool';

    /**
     * {@inheritDoc}
     */
    protected function displayFields($fields, array $meta = array())
    {
        $record = array();
        foreach ($fields as $item) {
            $record[$item['id']] = array(
                'title' => $item['title'],
                'value' => $item['identifier'],
            );
        }
        $result = array($record);

        return $result;
    }
}
