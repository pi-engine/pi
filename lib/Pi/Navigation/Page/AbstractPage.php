<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Pi\Navigation\Page;

use Pi;
use Traversable;
use Zend\Navigation\Page\AbstractPage as ZendAbstractPage;
use Zend\Navigation\Exception;
use Zend\Stdlib\ArrayUtils;

/**
 * Abstract page class
 *
 * {@inheritDoc}
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
abstract class AbstractPage extends ZendAbstractPage
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
                            'is not an instance of Zend\Navigation\Page',
                            $type
                        )
                    );
                }

                return $page;
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
