<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
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
    protected function displayFields($fields, array $meta = [])
    {
        $record = [];
        foreach ($fields as $item) {
            $record[$item['id']] = [
                'title' => $item['title'],
                'value' => $item['identifier'],
            ];
        }
        $result = [$record];

        return $result;
    }
}
