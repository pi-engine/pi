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

    /** @var string Form class */
    protected $form = '';

    /** @var string File to form template */
    protected $template = '';

    /** @var string Form filter class */
    protected $filter = '';


    /**
     * {@inheritDoc}
     */
    public function get($uid, $filter = false)
    {
        $result = parent::get($uid);
        if ($filter) {
            $data = array();
            foreach ($result as $item) {
                $data[] = array(
                    'title' => $item['title'],
                    'value' => $item['identifier'],
                );
            }
        }

        return $result;
    }

    /**
     * {@inheritDoc}
     */
    public function mget($uids, $filter = false)
    {
        $result = parent::mget($uids);

        return $result;
    }

}
