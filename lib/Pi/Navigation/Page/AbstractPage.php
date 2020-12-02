<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 */

namespace Pi\Navigation\Page;

use Traversable;
use Laminas\Navigation\Exception;
use Laminas\Navigation\Page\AbstractPage as LaminasAbstractPage;
use Laminas\Stdlib\ArrayUtils;

/**
 * Abstract page class
 *
 * {@inheritDoc}
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
abstract class AbstractPage extends LaminasAbstractPage
{
    /**
     * {@inheritDoc}
     */
    public static function factory($options)
    {
        if ($options instanceof Traversable) {
            $options = ArrayUtils::iteratorToArray($options);
        }

        if (!is_array($options)) {
            throw new Exception\InvalidArgumentException(
                'Invalid argument: $options must be an array or Traversable'
            );
        }

        if (isset($options['type'])) {
            $type = $options['type'];
            if (is_string($type) && !empty($type)) {
                switch (strtolower($type)) {
                    case 'mvc':
                        $type = 'Pi\Navigation\Page\Mvc';
                        break;
                    case 'uri':
                        $type = 'Pi\Navigation\Page\Uri';
                        break;
                }

                if (!class_exists($type, true)) {
                    throw new Exception\InvalidArgumentException(
                        'Cannot find class ' . $type
                    );
                }

                $page = new $type($options);
                if (!$page instanceof self) {
                    throw new Exception\InvalidArgumentException(
                        sprintf(
                            'Invalid argument: Detected type "%s", which ' .
                            'is not an instance of Laminas\Navigation\Page',
                            $type
                        )
                    );
                }

                return $page;
            }
        }

        if (static::$factories) {
            foreach (static::$factories as $factoryCallBack) {
                if (($page = call_user_func($factoryCallBack, $options))) {
                    return $page;
                }
            }
        }

        /**#@+
         * Modified by Taiwen Jiang
         */
        /*
        $hasUri = isset($options['uri']);
        $hasMvc = isset($options['action']) || isset($options['controller'])
                || isset($options['route']);
        */
        $hasUri = true;
        $hasMvc = !empty($options['action']) || !empty($options['controller'])
            || !empty($options['route']);
        /**#@-*/

        if ($hasMvc) {
            return new Mvc($options);
        } elseif ($hasUri) {
            return new Uri($options);
        } else {
            throw new Exception\InvalidArgumentException(
                'Invalid argument: Unable to determine class to instantiate'
            );
        }
    }
}
