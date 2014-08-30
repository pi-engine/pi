<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Pi\Mvc\Router\Http;

use Zend\Mvc\Router\Http\RouteMatch;
use Zend\Stdlib\RequestInterface as Request;

/**
 * Feed route
 *
 * Use cases:
 *
 * - Same structure, key-value and param delimiters:
 *   - Full mode: feed/module/controller/action/key1/val1/key2/val2?type=atom-or-rss
 *   - Full structure only: feed/module/controller/action?type=atom-or-rss
 *   - Module with default structure: feed/module?type=atatom-or-rssom
 * - Same structure and param delimiters:
 *   - Full mode: feed/module/controller/action/key1-val1/key2-val2?type=atom-or-rss
 *   - Full structure only: feed/module/controller/action?type=atom-or-rss
 * - Different structure delimiter:
 *   - Full mode:
 *      feed/module-controller-action/key1/val1/key2/val2?type=atom-or-rss;
 *      feed/module-controller-action/key1-val2/key2-val2?type=atom-or-rss
 *   - Default structure and parameters:
 *      feed/module/key1/val1/key2/val2?type=atom-or-rss;
 *      feed/module/key1-val1/key2-val2?type=atom-or-rss
 *   - Default structure: feed/module-controller?type=atom-or-rss
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Feed extends Standard
{
    /**
     * {@inheritDoc}
     */
    protected $prefix = '/feed';
}
