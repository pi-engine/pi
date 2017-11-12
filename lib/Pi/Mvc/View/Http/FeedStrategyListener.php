<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
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
