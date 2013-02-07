<?php
/**
 * Form class
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

use Zend\Form\Form as ZendForm;

class Form extends ZendForm
{
    /**
     * Grouping field names for rendering
     * @var array
     *       array(
     *          'groupName' => array(
     *              'label'     => 'Group Label',
     *              'elements'  => array('elementName', 'elementName', 'elementName', ...),
     *          ),
     *          'groupName' => array(
     *              'label'     => 'Group Label',
     *              'elements'  => array('elementName', 'elementName', 'elementName', ...),
     *          ),
     *      );
     */
    protected $groups;

    /**
     * Constructor
     *
     * @param null|string|int $name Optional name for the element
     */
    public function __construct($name = null)
    {
        parent::__construct($name);
        $this->init();
    }

    /**
     * Prepare elements for the form, optional
     */
    public function init() {}

    /**
     * Retrieve composed form factory
     *
     * Lazy-loads one if none present.
     *
     * @return Factory
     */
    public function getFormFactory()
    {
        if (null === $this->factory) {
            $this->setFormFactory(new Factory());
        }
        return $this->factory;
    }

    /**
     * Get list of elements for form view
     *
     * @return array
     */
    public function elementList()
    {
        $elements = array(
            'active'    => array(),
            'hidden'    => array(),
            'submit'    => '',
        );

        foreach ($this->byName as $key => $value) {
            $type = $value->getAttribute('type');
            if ('submit' == $type) {
                $elements['submit'] = $value;
            } elseif ('hidden' == $type) {
                $elements['hidden'][] = $value;
            } else {
                $elements['active'][] = $value;
            }
        }

        return $elements;
    }

    /**
     * Set grouped list
     *
     * @param array $groups
     * @return Form
     */
    public function setGroups(array $groups)
    {
        $this->groups = $groups;
        return $this;
    }

    /**
     * Get grouped list
     *
     * @return array
     */
    public function getGroups()
    {
        return $this->groups;
    }

    /**
     * Get assembled message
     * @param bool $OnlyHidden  Return only hidden field messages
     * @param string $delimiter
     * @return string
     */
    public function getMessage($OnlyHidden = true, $delimiter = '; ')
    {
        $messages = $this->getMessages();
        $list = array();
        foreach ($messages as $name => $messages) {
            if (!$messages) {
                continue;
            }
            if ($OnlyHidden && 'hidden' != $this->get($name)->getAttribute('type')) {
                continue;
            }
            $list[] = implode($delimiter, array_values($messages));
        }
        return implode($delimiter, $list);
    }
}
