<?php
/**
 * Form factory class
 *
 * You may not change or alter any portion of this comment or credits
 * of supporting developers from this source code or any supporting source code
 * which is considered copyrighted (c) material of the original comment or credit authors.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * @copyright       Copyright (c) Pi Engine http://www.xoopsengine.org
 * @license         http://www.xoopsengine.org/license New BSD License
 * @author          Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 * @package         Pi\Form
 */
namespace Pi\Form;

use Pi\Filter\FilterChain;
use Zend\Form\Factory as ZendFactory;
use Zend\InputFilter\Factory as InputFilterFactory;

class Factory extends ZendFactory
{
    /**
     * @{inheritdoc}
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
     * @{inheritdoc}
     */
    public function create($spec)
    {
        // Canonize type
        if (isset($spec['type']) && is_string($spec['type']) && false === strpos($spec['type'], '\\')) {
            $type = strtolower($spec['type']);
            if ($type == 'form' || $type == 'fieldset') {
                $spec['type'] = sprintf('%s\\%s', __NAMESPACE__, ucfirst($type));
            } else {
                $type = sprintf('%s\Element\\%s', __NAMESPACE__, ucfirst($spec['type']));
                if (!class_exists($type)) {
                    $type = sprintf('Zend\Form\Element\\%s', ucfirst($spec['type']));
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

    /**#@+
     * Deprecated methods
     */
    /**
     * {@inheritdoc}
     */
    public function ____createElement($spec)
    {
        if (isset($spec['type']) && !class_exists($spec['type'])) {
            $type = __NAMESPACE__ . '\Element\\' . ucfirst($spec['type']);
            if (!class_exists($type)) {
                $type = 'Zend\Form\Element\\' . ucfirst($spec['type']);
                if (class_exists($type)) {
                    $spec['type'] = $type;
                }
            } else {
                $spec['type'] = $type;
            }
        }
        return parent::createElement($spec);
    }

    /**
     * {@inheritdoc}
     */
    public function ____createFieldset($spec)
    {
        if (isset($spec['type']) && !class_exists($spec['type'])) {
            $type = __NAMESPACE__ . '\Fieldset\\' . ucfirst($spec['type']);
            if (!class_exists($type)) {
                $type = 'Zend\Form\Fieldset\\' . ucfirst($spec['type']);
                if (class_exists($type)) {
                    $spec['type'] = $type;
                }
            } else {
                $spec['type'] = $type;
            }
        }
        return parent::createFieldset($spec);
    }

    /**
     * {@inheritdoc}
     */
    public function ____createForm($spec)
    {
        if (isset($spec['type']) && !class_exists($spec['type'])) {
            $type = __NAMESPACE__ . '\Form\\' . ucfirst($spec['type']);
            if (!class_exists($type)) {
                $type = 'Zend\Form\Form\\' . ucfirst($spec['type']);
                if (class_exists($type)) {
                    $spec['type'] = $type;
                }
            } else {
                $spec['type'] = $type;
            }
        }
        return parent::createForm($spec);
    }
    /**#@-*/
}
