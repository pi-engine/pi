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

use Zend\Form\Form as ZendForm;

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
        foreach ($messages as $name => $messages) {
            if (!$messages) {
                continue;
            }
            if ($OnlyHidden
                && 'hidden' != $this->get($name)->getAttribute('type')
            ) {
                continue;
            }
            $list[] = implode($delimiter, array_values($messages));
        }

        return implode($delimiter, $list);
    }
}
