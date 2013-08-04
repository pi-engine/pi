<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 * @package         Form
 */

namespace Pi\Form;

use Pi\Filter\FilterChain;
use Zend\Form\Factory as ZendFactory;
use Zend\InputFilter\Factory as InputFilterFactory;

/**
 * Form factory
 *
 * {@inheritDoc}
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Factory extends ZendFactory
{
    /**
     * Get InputFilter factory
     *
     * @{inheritdoc}
     * @return InputFilterFactory
     */
    public function getInputFilterFactory()
    {
        $factory = parent::getInputFilterFactory();
        // Ensure Pi\Filter\FilterChain is used
        if (!$factory->getDefaultFilterChain() instanceof FilterChain) {
            $factory->setDefaultFilterChain(new FilterChain);
        }
        return $factory;
    }

    /**
     * Create a form or form element
     *
     * @{inheritdoc}
     */
    public function create($spec)
    {
        // Canonize type
        if (isset($spec['type']) && is_string($spec['type'])
            && false === strpos($spec['type'], '\\')) {
            $type = strtolower($spec['type']);
            if ($type == 'form' || $type == 'fieldset') {
                $spec['type'] = sprintf('%s\\%s',
                    __NAMESPACE__, ucfirst($type));
            } else {
                $type = sprintf('%s\Element\\%s',
                    __NAMESPACE__, ucfirst($spec['type']));
                if (!class_exists($type)) {
                    $type = sprintf('Zend\Form\Element\\%s',
                        ucfirst($spec['type']));
                    if (class_exists($type)) {
                        $spec['type'] = $type;
                    }
                } else {
                    $spec['type'] = $type;
                }
            }
        }

        return parent::create($spec);
    }
}
