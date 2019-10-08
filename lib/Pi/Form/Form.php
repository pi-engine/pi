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

use Pi;
use Traversable;
use Zend\Form\Exception;
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
    public function __construct($name = null, $options = [])
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

        $isValid = parent::isValid();

        /** @var \Pi\I18n\Translator\Translator $translator */
        $translator = \Pi::service('i18n')->getTranslator();

        $messages = $this->getMessages();
        foreach($this->getMessages() as $name => $messageGroup){
            foreach($messageGroup as $keyMessage => $message){
                $messages[$name][$keyMessage] = $translator->translate($message);
            }
        }

        $this->setMessages($messages);

        return $isValid;
    }

    /**
     * Prepare elements for the form, optional
     *
     * @return void
     */
    public function init()
    {
    }

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
        $elements = [
            'active' => [],
            'hidden' => [],
            'submit' => [],
        ];

        foreach ($this->elements as $key => $value) {
            $type = $value->getAttribute('type');
            if ('submit' == $type) {
                $elements['submit'][] = $value;
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
            $messages = [];
            foreach ($this->elements as $name => $element) {
                if ('hidden' != $element->getAttribute('type')) {
                    continue;
                }
                $messageSet = $element->getMessages();
                if (!is_array($messageSet)
                    && !$messageSet instanceof Traversable
                    || empty($messageSet)
                ) {
                    continue;
                }
                $messages[$name] = (array)$messageSet;
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
     * @param bool $OnlyHidden Return only hidden field messages
     * @param string $delimiter
     * @return string
     */
    public function getMessage($OnlyHidden = true, $delimiter = '; ')
    {
        $messages = $this->getMessages();
        $list     = [];
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
