<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Module\Page\Form;

use Pi;
use Pi\Form\Form as BaseForm;

class PageForm extends BaseForm
{
    protected $markup = 'text';

    /**
     * Constructor
     *
     * @param null|string|int $name Optional name for the element
     * @param string $markup Page type: text, html, markdown
     */
    public function __construct($name = null, $markup = null)
    {
        $this->markup = $markup ?: $this->markup;
        parent::__construct($name);
    }

    public function getInputFilter()
    {
        if (!$this->filter) {
            $this->filter = new PageFilter;
        }

        return $this->filter;
    }

    public function init()
    {
        $this->add(array(
            'name'          => 'title',
            'options'       => array(
                'label' => __('Page title'),
            ),
            'attributes'    => array(
                'type'  => 'text',
            )
        ));

        $this->add(array(
            'name'          => 'name',
            'options'       => array(
                'label' => __('Unique name'),
            ),
            'attributes'    => array(
                'type'  => 'text',
                'description'   => __('For named call and custom page settings; Only alphabet, number and underscore allowed.'),
            ),
        ));

        /*
        $this->add(array(
            'name'          => 'url',
            'options'       => array(
                'label' => __('URL'),
            ),
            'attributes'    => array(
                'type'          => 'text',
                'description'   => __('URL relative to www root.'),
            )
        ));
        */

        $this->add(array(
            'name'          => 'slug',
            'options'       => array(
                'label' => __('SEO slug'),
            ),
            'attributes'    => array(
                'type'          => 'text',
                'description'   => __('Unique slug for SEO.'),
            )
        ));

        $this->add(array(
            'name'          => 'active',
            'type'          => 'checkbox',
            'options'       => array(
                'label' => __('Active'),
            ),
            'attributes'    => array(
                'value'     => '1',
            )
        ));

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
                'class'         => 'span6',
            ),
        ));

        $this->add(array(
            'name'          => 'id',
            'attributes'    => array(
                'type'  => 'hidden',
                'value' => 0,
            )
        ));

        $this->add(array(
            'name'          => 'markup',
            'attributes'    => array(
                'type'  => 'hidden',
                'value' => $this->markup,
            )
        ));

        $this->add(array(
            'name'          => 'submit',
            'type'          => 'submit',
            'attributes'    => array(
                'value' => __('Submit'),
            )
        ));
    }
}
