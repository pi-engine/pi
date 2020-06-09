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
use Laminas\InputFilter\InputFilter;

/**
 * Filter and valid of draft edit form
 *
 * @author Zongshu Lin <lin40553024@163.com>
 */
class DraftEditFilter extends InputFilter
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
     * Initialize class and filter
     *
     * @param array $options
     */
    public function __construct($options = [])
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
            $this->items = DraftEditForm::getDefaultElements($this->mode);
        }

        $filterParams = $this->getFilterParameters();
        foreach (array_keys($filterParams) as $name) {
            if (in_array($name, $this->items)) {
                $this->add($filterParams[$name]);
            }
        }

        $this->add($filterParams['id']);
        $this->add($filterParams['fake_id']);
        $this->add($filterParams['uid']);
        $this->add($filterParams['time_publish']);
        $this->add($filterParams['time_update']);
        $this->add($filterParams['time_submit']);
        $this->add($filterParams['article']);
        $this->add($filterParams['jump']);
    }

    /**
     * Get filter parameters
     *
     * @return array
     */
    protected function getFilterParameters()
    {
        $module = Pi::service('module')->current();
        $config = Pi::config('', $module);

        $parameters = [
            'subject' => [
                'name'     => 'subject',
                'required' => false,
                'filters'  => [
                    [
                        'name' => 'StringTrim',
                    ],
                ],
            ],

            'subtitle' => [
                'name'     => 'subtitle',
                'required' => false,
                'filters'  => [
                    [
                        'name' => 'StringTrim',
                    ],
                ],
            ],

            'image' => [
                'name'     => 'image',
                'required' => false,
            ],

            'uid' => [
                'name'     => 'uid',
                'required' => false,
            ],

            'author' => [
                'name'     => 'author',
                'required' => false,
            ],

            'source' => [
                'name'     => 'source',
                'required' => false,
            ],

            'category' => [
                'name'     => 'category',
                'required' => false,
            ],

            'related' => [
                'name'     => 'related',
                'required' => false,
            ],

            'slug' => [
                'name'     => 'slug',
                'required' => false,
                'filters'  => [
                    [
                        'name' => 'StringTrim',
                    ],
                ],
            ],

            'seo_title' => [
                'name'     => 'seo_title',
                'required' => false,
            ],

            'seo_keywords' => [
                'name'     => 'seo_keywords',
                'required' => false,
            ],

            'seo_description' => [
                'name'     => 'seo_description',
                'required' => false,
            ],

            'time_publish' => [
                'name'     => 'time_publish',
                'required' => false,
            ],

            'time_update' => [
                'name'     => 'time_update',
                'required' => false,
            ],

            'time_submit' => [
                'name'     => 'time_submit',
                'required' => false,
            ],

            'content' => [
                'name'     => 'content',
                'required' => false,
            ],

            'id' => [
                'name'     => 'id',
                'required' => false,
            ],

            'fake_id' => [
                'name'     => 'fake_id',
                'required' => false,
            ],

            'article' => [
                'name'     => 'article',
                'required' => false,
            ],

            'jump' => [
                'name'     => 'jump',
                'required' => false,
            ],
        ];

        if ($config['enable_summary']) {
            $parameters['summary'] = [
                'name'     => 'summary',
                'required' => false,
            ];
        }

        if ($config['enable_tag']) {
            $parameters['tag'] = [
                'name'     => 'tag',
                'required' => false,
            ];
        }

        return $parameters;
    }
}
