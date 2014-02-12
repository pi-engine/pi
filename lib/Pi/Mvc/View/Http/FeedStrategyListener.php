<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Pi\Mvc\View\Http;

use Pi;

/**
 * Feed view strategy listener
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class FeedStrategyListener extends ViewStrategyListener
{
    /**
     * {@inheritDoc}
     */
    protected $type = 'feed';
}
