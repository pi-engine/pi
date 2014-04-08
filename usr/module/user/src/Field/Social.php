<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Module\User\Field;

/**
 * Social tool handler
 *
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Social extends CustomCompoundHandler
{
    /** @var string Field name and table name */
    protected $name = 'social';

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
