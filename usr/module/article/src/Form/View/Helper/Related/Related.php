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
            $relatedIds = explode(',', $value);
            $related    = Entity::getArticlePage(
                array('id' => $relatedIds), 
                1,
                count($related)
            );
        }
        
        $this->assign(array(
            'enable_tag' => Pi::api('form', $module)->isDisplayField('tag'),
            'url'        => $url,
            'related'    => $related,
        ));

        return $this->getTemplate($element, 'related');
    }
}
