<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Module\Message\Form;

use Pi;
use Pi\Form\Form as BaseForm;
use Zend\Form\Form;
use Zend\Form\Element;

/**
 * Form of reply message
 *
 * @author Xingyu Ji <xingyu@eefocus.com>
 */
class ReplyForm extends BaseForm
{
    /**
     * Editor type
     *
     * @var string
     */
    protected $markup = 'text';

    /**
     * Constructor
     *
     * @param null|string|int $name   Optional name for the element
     * @param string          $markup Page type: text, html, markdown
     */
    public function __construct($name = null, $markup = null)
    {
        $this->markup = $markup ?: $this->markup;
        parent::__construct($name);
    }

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        $set = '';
        switch ($this->markup) {
            case 'html':
                $editor         = 'html';
                break;
            case 'markdown':
                $editor         = 'markitup';
                $set            = 'markdown';
                break;
            case 'text':
            default:
                $editor         = 'textarea';
                break;
        }

        $this->add(array(
            'name'          => 'content',
            'options'       => array(
                'label'     => __('Content'),
                'editor'    => $editor,
                'set'       => $set,
            ),
            'attributes'    => array(
                'type'          => 'editor',
            ),
        ));

        $this->add(array(
            'name'          => 'uid_to',
            'attributes'    => array(
                'type'          => 'hidden',
            ),
        ));

        $this->add(array(
            'name'          => 'submit',
            'attributes'    => array(
                'type'  => 'submit',
                'value' => __('Send'),
            )
        ));
    }
}
