<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 */

namespace Module\Page\Form;

use Pi\Form\Form as BaseForm;

class PageForm extends BaseForm
{
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

    public function getInputFilter()
    {
        if (!$this->filter) {
            $this->filter = new PageFilter;
        }

        return $this->filter;
    }

    public function init()
    {
        $this->add(
            [
                'name'       => 'title',
                'options'    => [
                    'label' => _a('Page title'),
                ],
                'attributes' => [
                    'type' => 'text',
                ],
            ]
        );

        $this->add(
            [
                'name'       => 'name',
                'options'    => [
                    'label' => _a('Unique name'),
                ],
                'attributes' => [
                    'type'        => 'text',
                    'description' => _a('Only alphabet, number and underscore allowed.'),
                ],
            ]
        );

        $this->add(
            [
                'name'       => 'slug',
                'options'    => [
                    'label' => _a('SEO slug'),
                ],
                'attributes' => [
                    'type'        => 'text',
                    'description' => _a('Unique slug for SEO URL, space is not allowed.'),
                ],
            ]
        );

        $this->add(
            [
                'name'       => 'theme',
                'type'       => 'theme',
                'options'    => [
                    'allow_auto' => true,
                ],
                'attributes' => [
                    'description' => _a('The eligible theme layouts are not visible in the select list until this form is saved once with the selected theme'),
                ],
            ]
        );

        $this->add(
            [
                'name'    => 'layout',
                'type'    => 'layout',
                'options' => [
                    'theme' => '', // Specify theme name here
                ],
            ]
        );

        $this->add(
            [
                'name'    => 'template',
                'type'    => 'select',
                'options' => [
                    'label'         => __('Template'),
                    'value_options' => [
                        'page-view-simple' => __('Simple'),
                        'page-view'        => __('Default ( By panel )'),
                    ],
                ],
            ]
        );

        if ('phtml' == $this->markup) {
            $this->add(
                [
                    'name'       => 'content',
                    'options'    => [
                        'label' => _a('Template name'),
                    ],
                    'attributes' => [
                        'description' => _a(
                            'Select a template from `usr/custom/module/page/template/front/` w/o extension. You can also locate it in `usr/themes/yourtheme/custom/page/`'
                        ),
                    ],
                ]
            );
        } else {
            $set = '';
            switch ($this->markup) {
                case 'html':
                    $editor = 'html';
                    break;
                case 'markdown':
                    $editor = 'markitup';
                    $set    = 'markdown';
                    break;
                case 'text':
                default:
                    $editor = 'textarea';
                    break;
            }

            $this->add(
                [
                    'name'       => 'content',
                    'type'       => 'editor',
                    'options'    => [
                        'label'  => _a('Content'),
                        'editor' => $editor,
                        'set'    => $set,
                    ],
                    'attributes' => [
                        'rows' => 5,
                    ],
                ]
            );
        }

        // extra_seo
        $this->add(
            [
                'name'    => 'extra_seo',
                'type'    => 'fieldset',
                'options' => [
                    'label' => _a('SEO settings'),
                ],
            ]
        );

        // seo_title
        $this->add(
            [
                'name'       => 'seo_title',
                'options'    => [
                    'label' => _a('SEO Title'),
                ],
                'attributes' => [
                    'type'        => 'text',
                    'description' => _a('To be used for HTML head meta.'),
                ],
            ]
        );

        // seo_keywords
        $this->add(
            [
                'name'       => 'seo_keywords',
                'options'    => [
                    'label' => _a('SEO Keywords'),
                ],
                'attributes' => [
                    'type'        => 'text',
                    'description' => _a('To be used for HTML head meta.'),
                ],
            ]
        );

        // seo_description
        $this->add([
            'name'       => 'seo_description',
            'options'    => [
                'label' => _a('SEO Description'),
            ],
            'attributes' => [
                'rows' => '3',
                'cols' => '40',
                'type'        => 'textarea',
                'description' => _a('To be used for HTML head meta.'),
            ],
        ]);

        // Enable for online
        $this->add(
            [
                'name'       => 'active',
                'type'       => 'checkbox',
                'options'    => [
                    'label' => _a('Active'),
                ],
                'attributes' => [
                    'value' => '1',
                ],
            ]
        );

        $this->add(
            [
                'name'       => 'id',
                'attributes' => [
                    'type'  => 'hidden',
                    'value' => 0,
                ],
            ]
        );

        $this->add(
            [
                'name'       => 'markup',
                'attributes' => [
                    'type'  => 'hidden',
                    'value' => $this->markup,
                ],
            ]
        );

        $this->add(
            [
                'name'       => 'submit',
                'type'       => 'submit',
                'attributes' => [
                    'value' => __('Submit'),
                ],
            ]
        );
    }
}
