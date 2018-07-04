<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 * @package         View
 */

namespace Pi\View\Helper;

use stdClass;
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
     * if true, disable escaping
     *
     * @var bool
     */
    protected $_disableEscape = false;
     
    /**
     * {@inheritDoc}
     */
    public function __invoke(
        $content = null,
        $keyValue = null,
        $keyType = 'name',
        $modifiers = [],
        $placement = null
    )
    {
        if (null === $placement) {
            $placement = Placeholder\Container\AbstractContainer::SET;
        }
        parent::__invoke($content, $keyValue, $keyType, $modifiers, $placement);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function itemToString(stdClass $item)
    {
        // Skip `property` rendering for invalid doctype
        if ('property' === $item->type) {
            if (!$this->view->plugin('doctype')->isRdfa()) {
                return '';
            }
        }
        if ((property_exists($item, 'property') && $item->property == 'og:title') || (property_exists($item, 'name') && $item->name == 'twitter:title')) {
            $this->_disableEscape = true;
        } else {
            $this->_disableEscape = false;
        }
        return parent::itemToString($item);
    }
    
    /**
     * {@inheritDoc}
     */
    public function escape($str) {
        if ($this->_disableEscape) {
            return $str;
        }
        return parent::escape($str);
    }

    /**
     * {@inheritDoc}
     */
    protected function isValid($item)
    {
        // Skip doctype check for setting `property`
        if ($item instanceof stdClass && 'property' === $item->type) {
            if (!isset($item->modifiers) || !isset($item->content)) {
                return false;
            }
            return true;
        }

        return parent::isValid($item);
    }

    /**
     * Append an element
     *
     * @param stdClass $value
     * @return void
     */
    public function append($value)
    {
        if ('name' == $value->type) {
            $container = $this->getContainer();
            $content   = '';
            foreach ($container->getArrayCopy() as $index => $item) {
                if ('name' == $item->type && $item->name == $value->name) {
                    $content = $item->content;
                    $this->offsetUnset($index);
                }
            }
            if ($content) {
                $separator      = ('description' == $value->name) ? ' ' : ', ';
                $value->content = $content . $separator . $value->content;
            }
        }

        return parent::append($value);
    }

    /**
     * Prepend an element
     *
     * @param stdClass $value
     * @return void
     */
    public function prepend($value)
    {
        if ('name' == $value->type) {
            $container = $this->getContainer();
            $content   = '';
            foreach ($container->getArrayCopy() as $index => $item) {
                if ('name' == $item->type && $item->name == $value->name) {
                    $content = $item->content;
                    $this->offsetUnset($index);
                }
            }
            if ($content) {
                $separator      = ('description' == $value->name) ? ' ' : ', ';
                $value->content = $value->content . $separator . $content;
            }
        }

        return parent::prepend($value);
    }
}
