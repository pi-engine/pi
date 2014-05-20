<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 * @package         Form
 */

namespace Pi\Form;

use Pi;
use Traversable;
use Zend\Form\Form as ZendForm;
use Zend\Form\Exception;

/**
 * Form class
 *
 * Added group support for rendering
 *
 * ```
 *       array(
 *          <group-name> => array(
 *              'label'     => 'Group Label',
 *              'elements'  => array('elementName', 'elementName',
 *                  'elementName', <...>),
 *          ),
 *          <group-name> => array(
 *              'label'     => 'Group Label',
 *              'elements'  => array('elementName', 'elementName',
 *                  'elementName', <...>),
 *          ),
 *      );
 * ```
 *
 * {@inheritDoc}
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Form extends ZendForm
{
    /**
     * Grouping field names for rendering
     * @var array
     */
    protected $groups;

    /**
     * Constructor
     *
     * {@inheritDoc}
     */
    public function __construct($name = null, $options = array())
    {
        parent::__construct($name, $options);
        $this->init();
    }

    /**
     * {@inheritDoc}
     */
    public function getFormFactory()
    {
        if (null === $this->factory) {
            $this->setFormFactory(new Factory());
        }

        return $this->factory;
    }

    /**
     * {@inheritDoc}
     *
     * Load translation for validators
     */
    public function isValid()
    {
        if ($this->hasValidated) {
            return $this->isValid;
        }

        Pi::service('i18n')->load('validator');

        return parent::isValid();
    }

    /**
     * Prepare elements for the form, optional
     *
     * @return void
     */
    public function init() {}

    /**
     * Get list of elements
     *
     * Element list associated with active, hidden, and submit
     *
     *  - active: string[]
     *  - hidden: string[]
     *  - submit: string
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
     * @return $this
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
     * Get messages for hidden elements
     *
     * @param  null|string $elementName
     *
     * @throws Exception\InvalidArgumentException
     * @return array
     */
    public function getHiddenMessages($elementName = null)
    {
        if (null === $elementName) {
            $messages = array();
            foreach ($this->byName as $name => $element) {
                if ('hidden' != $element->getAttribute('type')) {
                    continue;
                }
                $messageSet = $element->getMessages();
                if (!is_array($messageSet)
                    && !$messageSet instanceof Traversable
                    || empty($messageSet)) {
                    continue;
                }
                $messages[$name] = (array) $messageSet;
            }
            return $messages;
        }

        if (!$this->has($elementName)) {
            throw new Exception\InvalidArgumentException(sprintf(
                'Invalid element name "%s" provided to %s',
                $elementName,
                __METHOD__
            ));
        }

        $element = $this->get($elementName);
        return $element->getMessages();
    }

    /**
     * Get assembled message
     *
     * @param bool      $OnlyHidden  Return only hidden field messages
     * @param string    $delimiter
     * @return string
     */
    public function getMessage($OnlyHidden = true, $delimiter = '; ')
    {
        $messages = $this->getMessages();
        $list = array();
        foreach ($messages as $name => $msgs) {
            if (!$msgs) {
                continue;
            }
            if ($OnlyHidden
                && 'hidden' != $this->get($name)->getAttribute('type')
            ) {
                continue;
            }
            $list[] = implode($delimiter, array_values($msgs));
        }

        return implode($delimiter, $list);
    }
}
