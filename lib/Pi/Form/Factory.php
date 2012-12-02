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
 * @since           3.0
 * @package         Pi\Form
 * @version         $Id$
 */
namespace Pi\Form;

use Pi\Filter\FilterChain;
use Zend\Form\Factory as ZendFactory;
use Zend\InputFilter\Factory as InputFilterFactory;
use ArrayAccess;
use Traversable;

class Factory extends ZendFactory
{
    /**
     * Get current input filter factory
     *
     * If none provided, uses an unconfigured instance.
     *
     * @return InputFilterFactory
     */
    public function getInputFilterFactory()
    {
        if (null === $this->inputFilterFactory) {
            $this->setInputFilterFactory(new InputFilterFactory());
            $this->inputFilterFactory->setDefaultFilterChain(new FilterChain);
        }
        return $this->inputFilterFactory;
    }

    /**
     * Create an element, fieldset, or form
     *
     * Introspects the 'type' key of the provided $spec, and determines what
     * type is being requested; if none is provided, assumes the spec
     * represents simply an element.
     *
     * @param  array|Traversable $spec
     * @return ElementInterface
     */
    public function create($spec)
    {
        // Canonize type
        if (isset($spec['type']) && is_string($spec['type']) && false === strpos($spec['type'], '\\')) {
            $type = strtolower($spec['type']);
            if ($type == 'form' || $type == 'fieldset') {
                $spec['type'] = sprintf('%s\\%s', __NAMESPACE__, ucfirst($type));
            } else {
                $type = sprintf('%s\\Element\\%s', __NAMESPACE__, ucfirst($spec['type']));
                if (!class_exists($type)) {
                    $type = sprintf('Zend\\Form\\Element\\%s', ucfirst($spec['type']));
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

    /**
     * Create an element based on the provided specification
     *
     * Specification can contain any of the following:
     * - type: the Element class to use; defaults to \Zend\Form\Element
     * - name: what name to provide the element, if any
     * - attributes: an array, Traversable, or ArrayAccess object of element
     *   attributes to assign
     *
     * @param  array|Traversable|ArrayAccess $spec
     * @return ElementInterface
     */
    public function createElement($spec)
    {
        if (isset($spec['type']) && !class_exists($spec['type'])) {
            $type = __NAMESPACE__ . '\\Element\\' . ucfirst($spec['type']);
            if (!class_exists($type)) {
                $type = 'Zend\\Form\\Element\\' . ucfirst($spec['type']);
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
     * Create a fieldset based on the provided specification
     *
     * Specification can contain any of the following:
     * - type: the Fieldset class to use; defaults to \Zend\Form\Fieldset
     * - name: what name to provide the fieldset, if any
     * - attributes: an array, Traversable, or ArrayAccess object of element
     *   attributes to assign
     * - elements: an array or Traversable object where each entry is an array
     *   or ArrayAccess object containing the keys:
     *   - flags: (optional) array of flags to pass to FieldsetInterface::add()
     *   - spec: the actual element specification, per {@link createElement()}
     *
     * @param  array|Traversable|ArrayAccess $spec
     * @return FieldsetInterface
     */
    public function createFieldset($spec)
    {
        if (isset($spec['type']) && !class_exists($spec['type'])) {
            $type = __NAMESPACE__ . '\\Fieldset\\' . ucfirst($spec['type']);
            if (!class_exists($type)) {
                $type = 'Zend\\Form\\Fieldset\\' . ucfirst($spec['type']);
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
     * Create a form based on the provided specification
     *
     * Specification follows that of {@link createFieldset()}, and adds the
     * following keys:
     *
     * @param  array|Traversable|ArrayAccess $spec
     * @return FormInterface
     * @throws Exception\InvalidArgumentException for an invalid $spec
     * @throws Exception\DomainException for an invalid form type
     */
    public function createForm($spec)
    {
        if (isset($spec['type']) && !class_exists($spec['type'])) {
            $type = __NAMESPACE__ . '\\Form\\' . ucfirst($spec['type']);
            if (!class_exists($type)) {
                $type = 'Zend\\Form\\Form\\' . ucfirst($spec['type']);
                if (class_exists($type)) {
                    $spec['type'] = $type;
                }
            } else {
                $spec['type'] = $type;
            }
        }
        return parent::createForm($spec);
    }
}
