<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link         http://code.piengine.org for the Pi Engine source repository
 * @copyright    Copyright (c) Pi Engine http://piengine.org
 * @license      http://piengine.org/license.txt BSD 3-Clause License
 */

namespace Module\Article\Form;

use Module\Article\Controller\Admin\SetupController as Config;
use Pi;
use Pi\Form\Form as BaseForm;

/**
 * Draft edit form class
 *
 * @author Zongshu Lin <lin40553024@163.com>
 */
class DraftEditForm extends BaseForm
{
    /**
     * The mode of displaying for elements
     * @var string
     */
    protected $mode = Config::FORM_MODE_EXTENDED;

    /**
     * Elements to display
     * @var array
     */
    protected $items = [];

    /**
     * Initialize object
     *
     * @param string $name Form name
     * @param array $options Optional parameters
     */
    public function __construct($name, $options = [])
    {
        if (isset($options['mode'])) {
            $this->mode = $options['mode'];
        }
        if (Config::FORM_MODE_CUSTOM == $this->mode) {
            $this->items = isset($options['elements'])
                ? $options['elements'] : [];
        } elseif (!empty($options['elements'])) {
            $this->items = $options['elements'];
        } else {
            $this->items = $this->getDefaultElements($this->mode);
        }
        parent::__construct($name);
    }

    /**
     * Get defined form element
     * !!! The value of each field must be the name of each form
     *
     * @return array
     */
    public static function getExistsFormElements()
    {
        return [
            'subject'         => __('Subject'),
            'subtitle'        => __('Subtitle'),
            'summary'         => __('Summary'),
            'content'         => __('Content'),
            'image'           => __('Image'),
            'author'          => __('Author'),
            'source'          => __('Source'),
            'category'        => __('Category'),
            'tag'             => __('Tag'),
            'related'         => __('Related'),
            'slug'            => __('Slug'),
            'seo_title'       => __('SEO Title'),
            'seo_keywords'    => __('SEO Keywords'),
            'seo_description' => __('SEO Description'),
        ];
    }

    public static function getNeededElements()
    {
        return ['subject', 'content', 'category'];
    }

    /**
     * Get default elements for displaying
     *
     * @param string $mode
     *
     * @return array
     */
    public static function getDefaultElements(
        $mode = Config::FORM_MODE_EXTENDED
    )
    {
        $normal = [
            'subject',
            'subtitle',
            'summary',
            'content',
            'image',
            'author',
            'source',
            'category',
            'tag',
        ];

        $extended = array_merge($normal, [
            'related',
            'slug',
            'seo_title',
            'seo_keywords',
            'seo_description',
        ]);

        return (Config::FORM_MODE_NORMAL == $mode) ? $normal : $extended;
    }

    /**
     * Initialize form element
     */
    public function init()
    {
        $module     = Pi::service('module')->current();
        $formParams = $this->getFormParameters();

        // Initializing form defined by user
        foreach (array_keys($formParams) as $name) {
            if (in_array($name, $this->items)) {
                $this->add($formParams[$name]);
            }
        }
        if (in_array('content', $this->items)) {
            $editorConfig = Pi::config()->load("module.{$module}.ckeditor.php");
            $editor       = $this->get('content');
            $editor->setOptions(array_merge(
                $editor->getOptions(),
                $editorConfig
            ));
        }

        // Initializing needed form
        $this->add($formParams['id']);
        $this->add($formParams['fake_id']);
        $this->add($formParams['uid']);
        $this->add($formParams['time_publish']);
        $this->add($formParams['time_update']);
        $this->add($formParams['time_submit']);
        $this->add($formParams['article']);
        $this->add($formParams['jump']);

        $this->add([
            'name' => 'security',
            'type' => 'csrf',
        ]);

        $this->add([
            'name'       => 'do_submit',
            'attributes' => [
                'value' => __('Save draft'),
            ],
            'type'       => 'submit',
        ]);
    }

    /**
     * Get form parameters
     *
     * @return array
     */
    protected function getFormParameters()
    {
        $module = Pi::service('module')->current();
        $config = Pi::config('', $module);

        switch ($config['markup']) {
            case 'html':
                $editor = 'html';
                $set    = '';
                break;
            case 'compound':
                $editor = 'markitup';
                $set    = 'html';
                break;
            case 'markdown':
                $editor = 'markitup';
                $set    = 'markdown';
                break;
            default:
                $editor = 'textarea';
                $set    = '';
        }

        $parameters = [
            'subject' => [
                'name'       => 'subject',
                'options'    => [
                    'label' => __('Subject'),
                ],
                'attributes' => [
                    'id'        => 'subject',
                    'type'      => 'text',
                    'data-size' => $config['max_subject_length'],
                ],
            ],

            'subtitle' => [
                'name'       => 'subtitle',
                'options'    => [
                    'label' => __('Subtitle'),
                ],
                'attributes' => [
                    'id'        => 'subtitle',
                    'type'      => 'text',
                    'data-size' => $config['max_subtitle_length'],
                ],
            ],

            'image' => [
                'name'       => 'image',
                'options'    => [
                    'label' => __('Image'),
                ],
                'attributes' => [
                    'id'   => 'image',
                    'type' => 'hidden',
                ],
            ],

            'uid' => [
                'name'       => 'uid',
                'options'    => [
                    'label' => __('Submitter'),
                ],
                'attributes' => [
                    'id'   => 'user',
                    'type' => 'hidden',
                ],
            ],

            'author' => [
                'name'       => 'author',
                'options'    => [
                    'label' => __('Author'),
                ],
                'attributes' => [
                    'id'   => 'author',
                    'type' => 'hidden',
                ],
            ],

            'source' => [
                'name'       => 'source',
                'options'    => [
                    'label' => __('Source'),
                ],
                'attributes' => [
                    'id'   => 'source',
                    'type' => 'text',
                ],
            ],

            'category' => [
                'name'    => 'category',
                'options' => [
                    'label' => __('Category'),
                ],
                'type'    => 'Module\Article\Form\Element\Category',
            ],

            'related' => [
                'name'       => 'related',
                'options'    => [
                    'label' => __('Related article'),
                ],
                'attributes' => [
                    'id'   => 'related',
                    'type' => 'hidden',
                ],
            ],

            'slug' => [
                'name'       => 'slug',
                'options'    => [
                    'label' => __('Slug'),
                ],
                'attributes' => [
                    'id'        => 'slug',
                    'type'      => 'text',
                    'data-size' => $config['max_subject_length'],
                ],
            ],

            'seo_title' => [
                'name'       => 'seo_title',
                'options'    => [
                    'label' => __('SEO title'),
                ],
                'attributes' => [
                    'id'   => 'seo_title',
                    'type' => 'text',
                ],
            ],

            'seo_keywords' => [
                'name'       => 'seo_keywords',
                'options'    => [
                    'label' => __('SEO keywords'),
                ],
                'attributes' => [
                    'id'   => 'seo_keywords',
                    'type' => 'text',
                ],
            ],

            'seo_description' => [
                'name'       => 'seo_description',
                'options'    => [
                    'label' => __('SEO description'),
                ],
                'attributes' => [
                    'id'   => 'seo_description',
                    'type' => 'textarea',
                ],
            ],

            'time_publish' => [
                'name'       => 'time_publish',
                'options'    => [
                    'label' => __('Publish time'),
                ],
                'attributes' => [
                    'type' => 'hidden',
                ],
            ],

            'time_update' => [
                'name'       => 'time_update',
                'options'    => [
                    'label' => __('Update time'),
                ],
                'attributes' => [
                    'type' => 'hidden',
                ],
            ],

            'time_submit' => [
                'name'       => 'time_submit',
                'options'    => [
                    'label' => __('Submit time'),
                ],
                'attributes' => [
                    'type' => 'hidden',
                ],
            ],

            'content' => [
                'name'       => 'content',
                'options'    => [
                    'label'  => __('Content'),
                    'editor' => $editor,
                    'set'    => $set,
                ],
                'attributes' => [
                    'id'   => 'content',
                    'type' => 'editor',
                ],
            ],

            'id' => [
                'name'       => 'id',
                'attributes' => [
                    'id'   => 'id',
                    'type' => 'hidden',
                ],
            ],

            'fake_id' => [
                'name'       => 'fake_id',
                'attributes' => [
                    'id'   => 'fake_id',
                    'type' => 'hidden',
                ],
            ],

            'article' => [
                'name'       => 'article',
                'attributes' => [
                    'id'   => 'article',
                    'type' => 'hidden',
                ],
            ],

            'jump' => [
                'name'       => 'jump',
                'attributes' => [
                    'id'    => 'jump',
                    'type'  => 'hidden',
                    'value' => '',
                ],
            ],
        ];

        if (!empty($config['enable_summary'])) {
            $parameters['summary'] = [
                'name'       => 'summary',
                'options'    => [
                    'label' => __('Summary'),
                ],
                'attributes' => [
                    'type'      => 'textarea',
                    'data-size' => $config['max_summary_length'],
                ],
            ];
        }

        if ($config['enable_tag']) {
            $parameters['tag'] = [
                'name'       => 'tag',
                'type'       => 'tag',
                'options'    => [
                    'label' => __('Tags'),
                ],
                'attributes' => [
                    'id' => 'tag',
                ],
            ];
        }

        return $parameters;
    }
}
