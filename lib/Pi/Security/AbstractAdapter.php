<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Pi\Security;

/**
 * Abstract security adapter class
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
abstract class AbstractAdapter
{
    /** @var string Prompt message */
    const MESSAGE = "DEFINE SPECIFIC MESSAGE";

    /**
     * Check against security settings
     *
     * Policy with different result:
     *
     * - true: following evaluations will be terminated and current request is approved
     * - false: following evaluations will be terminated and current request is denied
     * - null: continue
     *
     * @param array $options
     * @return bool|null
     */
    abstract public static function check($options = null);
}
