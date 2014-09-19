<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 * @package         Form
 */

namespace Module\Article\Form\View\Helper\Related;

use Module\Article\Form\View\Helper\AbstractCustomHelper;
use Zend\Form\ElementInterface;
use Module\Article\Entity;
use Pi;

/**
 * Related element helper of related compound
 *
 * @author Zongshu Lin <lin40553024@163.com>
 */
class Related extends AbstractCustomHelper
{
    /**
     * {@inheritDoc}
     */
    public function __invoke(ElementInterface $element = null)
    {
        if (!$element) {
            return $this;
        }

        return $this->render($element);
    }

    /**
     * {@inheritDoc}
     */
    public function render(ElementInterface $element)
    {
        $module = $element->getOption('module') ?:
            Pi::service('module')->current();
        
        $url = Pi::service('url')->assemble('default', array(
            'controller' => 'article',
            'action'     => 'get.fuzzy.article',
        ));
        
        $value   = $element->getValue();
        $related = $relatedIds = array();
        if (!empty($value)) {
            $relatedIds = array_flip($value);
            $related    = Entity::getArticlePage(
                array('id' => $value), 
                1
            );
            foreach ($related as $item) {
                if (array_key_exists($item['id'], $relatedIds)) {
                    $relatedIds[$item['id']] = $item;
                }
            }
            $related = array_filter($relatedIds, function($var) {
                return is_array($var);
            });
        }
        
        $attributes = $element->getAttributes();
        $this->assign(array(
            'enable_tag' => Pi::config('enable_tag', $module),
            'url'        => $url,
            'related'    => $related,
            'attributes' => $this->createAttributesString($attributes),
            'name'       => $element->getName(),
            'value'      => $element->getValue(),
        ));

        return $this->getTemplate($element);
    }
}
