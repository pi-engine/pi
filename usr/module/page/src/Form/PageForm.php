<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
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
                'label' => _a('Page title'),
            ),
            'attributes'    => array(
                'type'  => 'text',
            )
        ));

        $this->add(array(
            'name'          => 'name',
            'options'       => array(
                'label' => _a('Unique name'),
            ),
            'attributes'    => array(
                'type'  => 'text',
                'description'   => _a('Only alphabet, number and underscore allowed.'),
            ),
        ));

        $this->add(array(
            'name'          => 'slug',
            'options'       => array(
                'label' => _a('SEO slug'),
            ),
            'attributes'    => array(
                'type'          => 'text',
                'description'   => _a('Unique slug for SEO URL.'),
            )
        ));

        $this->add(array(
            'name'          => 'theme',
            'type'          => 'theme',
            'options'       => array(
                'allow_auto'    => true,
            ),
        ));

        $this->add(array(
            'name'          => 'layout',
            'type'          => 'layout',
            'options'       => array(
                'theme' => '', // Specify theme name here
            ),
        ));

        if ('phtml' == $this->markup) {
            $this->add(array(
                'name'          => 'content',
                'options'       => array(
                    'label' => _a('Template name'),
                ),
                'attributes'    => array(
                    'description'   => _a('Select a template from `usr/custom/module/page/template/front/` w/o extension.'),
                ),
            ));
        } else {
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
                'type'          => 'editor',
                'options'       => array(
                    'label'     => _a('Content'),
                    'editor'    => $editor,
                    'set'       => $set,
                ),
                'attributes'    => array(
                    'rows'         => 5,
                ),
            ));
        }

        // extra_seo
        $this->add(array(
            'name' => 'extra_seo',
            'type' => 'fieldset',
            'options' => array(
                'label' => _a('SEO settings'),
            ),
        ));

        // seo_title
        $this->add(array(
            'name' => 'seo_title',
            'options' => array(
                'label' => _a('SEO Title'),
            ),
            'attributes' => array(
                'type'          => 'text',
                'description'   => _a('To be used for HTML head meta.'),
            )
        ));

        // seo_keywords
        $this->add(array(
            'name' => 'seo_keywords',
            'options' => array(
                'label' => _a('SEO Keywords'),
            ),
            'attributes' => array(
                'type'          => 'text',
                'description'   => _a('To be used for HTML head meta.'),
            )
        ));

        // seo_description
        $this->add(array(
            'name' => 'seo_description',
            'options' => array(
                'label' => _a('SEO Description'),
            ),
            'attributes' => array(
                'type'          => 'text',
                'description'   => _a('To be used for HTML head meta.'),
            )
        ));

        // Enable for online
        $this->add(array(
            'name'          => 'active',
            'type'          => 'checkbox',
            'options'       => array(
                'label' => _a('Active'),
            ),
            'attributes'    => array(
                'value'     => '1',
            )
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
