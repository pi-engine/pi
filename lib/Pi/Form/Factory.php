<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 * @package         Form
 */

namespace Pi\Form;

use Pi\Filter\FilterChain;
use Laminas\Form\Factory as LaminasFactory;
use Laminas\InputFilter\Factory as InputFilterFactory;

/**
 * Form factory
 *
 * {@inheritDoc}
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Factory extends LaminasFactory
{
    /**
     * Get InputFilter factory
     *
     * @{inheritdoc}
     *
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
            && false === strpos($spec['type'], '\\')
        ) {
            $type = strtolower($spec['type']);
            if ($type == 'form' || $type == 'fieldset') {
                $spec['type'] = sprintf(
                    '%s\\%s',
                    __NAMESPACE__, ucfirst($type)
                );
            } else {
                $canonizedType = str_replace(
                    ' ',
                    '',
                    ucwords(
                        str_replace(
                            ['_', '-'],
                            ' ',
                            $spec['type']
                        )
                    )
                );
                $type          = sprintf(
                    '%s\Element\\%s',
                    __NAMESPACE__,
                    $canonizedType
                );
                if (class_exists($type)) {
                    $spec['type'] = $type;
                } else {
                    $type = sprintf(
                        'Laminas\Form\Element\\%s',
                        $canonizedType
                    );
                    if (class_exists($type)) {
                        $spec['type'] = $type;
                    } else {
                        if (!isset($spec['attributes']['type'])) {
                            $spec['attributes']['type'] = $spec['type'];
                        }
                        unset($spec['type']);
                    }
                }
            }
        }

        return parent::create($spec);
    }
}
