<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 */

namespace Pi\Mvc\Router\Http;

/**
 * API route
 *
 * Use cases:
 *
 * - Same structure, key-value and param delimiters:
 *   - Full mode: api/module/controller/action/key1/val1/key2/val2
 *   - Full structure only: api/module/controller/action
 *   - Module with default structure: api/module
 * - Same structure and param delimiters:
 *   - Full mode: api/module/controller/action/key1-val1/key2-val2
 *   - Full structure only: api/module/controller/action
 * - Different structure delimiter:
 *   - Full mode:
 *      api/module-controller-action/key1/val1/key2/val2;
 *      api/module-controller-action/key1-val2/key2-val2
 *   - Default structure and parameters:
 *      api/module/key1/val1/key2/val2;
 *      api/module/key1-val1/key2-val2
 *   - Default structure: api/module-controller
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Api extends Standard
{
    /**
     * {@inheritDoc}
     */
    protected $prefix = '/api';
}
