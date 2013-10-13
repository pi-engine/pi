<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Custom\User\Community;

use Pi;
use Pi\Application\AbstractActivity;

/**
 * User community list callback handler
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Community extends AbstractActivity
{
    /** @var string */
    protected $module = 'user';

    /**
     * {@inheritDoc}
     */
    public function get($uid, $limit, $offset = 0)
    {
        $remote = 'http://slave/api/service/community';
        $result = Pi::service('remote')->get($remote, array(
            'uid'   => $uid,
            'limit' => $limit,
        ));
        /*
        $result = array();
        for ($i = 1; $i <= $limit) {
            $url = 'http://www.eefocus.com/community/' . $i;
            $title = sprintf(__('Community #%d'), $i);
            $result[] = array(
                'time'      => null,
                'message'   => '<a href="' . $url
                            . '" title="" target="_blank">' . $title . '</a>',
            );
        }
        */

        return $result;
    }
}
