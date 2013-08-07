<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 * @package         View
 */

namespace Pi\View\Helper;

use Pi;
use Zend\View\Helper\HeadMeta as ZendHeadMeta;
use Zend\View\Helper\Placeholder;

/**
 * Helper for setting and building meta elements for HTML head section
 *
 * @todo To reset global meta for keywords/description
 * @see \Zend\View\Helper\HeadMeta for details.
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class HeadMeta extends ZendHeadMeta
{
    /**
     * Retrieve object instance; optionally add meta tag
     *
     * @param  string $content
     * @param  string $keyValue
     * @param  string $keyType
     * @param  array $modifiers
     * @param  string $placement
     * @return self
     */
    public function __invoke(
        $content = null,
        $keyValue = null,
        $keyType = 'name',
        $modifiers = array(),
        $placement = null
    ) {
        if (null === $placement) {
            $placement = Placeholder\Container\AbstractContainer::SET;
        }
        parent::__invoke($content, $keyValue, $keyType, $modifiers,
                         $placement);

        return $this;
    }

    /**
     * Append an element
     *
     * @param \stdClass $value
     * @return void
     */
    public function append($value)
    {
        if ('name' == $value->type) {
            $container = $this->getContainer();
            $content = '';
            foreach ($container->getArrayCopy() as $index => $item) {
                if ($item->name == $value->name) {
                    $content = $item->content;
                    $this->offsetUnset($index);
                }
            }
            if ($content) {
                $separator = ('description' == $value->name) ? ' ' : ', ';
                $value->content = $content . $separator . $value->content;
            }
        }

        return parent::append($value);
    }

    /**
     * Prepend an element
     *
     * @param \stdClass $value
     * @return void
     */
    public function prepend($value)
    {
        if ('name' == $value->type) {
            $container = $this->getContainer();
            $content = '';
            foreach ($container->getArrayCopy() as $index => $item) {
                if ($item->name == $value->name) {
                    $content = $item->content;
                    $this->offsetUnset($index);
                }
            }
            if ($content) {
                $separator = ('description' == $value->name) ? ' ' : ', ';
                $value->content = $value->content . $separator . $content;
            }
        }

        return parent::prepend($value);
    }
}
