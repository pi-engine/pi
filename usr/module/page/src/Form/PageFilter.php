<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 */

namespace Module\Page\Form;

use Module\Page\Validator;
use Pi;
use Laminas\InputFilter\InputFilter;

class PageFilter extends InputFilter
{
    public function __construct()
    {
        $this->add([
            'name'    => 'title',
            'filters' => [
                [
                    'name' => 'StringTrim',
                ],
            ],
        ]);

        $this->add([
            'name'       => 'name',
            'required'   => false,
            'filters'    => [
                [
                    'name' => 'StringTrim',
                ],
            ],
            'validators' => [
                [
                    'name'    => 'Regex',
                    'options' => [
                        'pattern' => '/[a-z0-9_]/',
                    ],
                ],
                new Validator\PageNameDuplicate(),
            ],
        ]);

        $this->add([
            'name'       => 'slug',
            'required'   => false,
            'filters'    => [
                [
                    'name' => 'StringTrim',
                ],
            ],
            'validators' => [
                [
                    'name'    => 'Regex',
                    'options' => [
                        'pattern' => '/[^\s]/',
                    ],
                ],
                new Validator\PageSlugDuplicate(),
            ],
        ]);

        $this->add([
            'name'    => 'markup',
            'filters' => [
                [
                    'name' => 'StringTrim',
                ],
            ],
        ]);

        $this->add([
            'name' => 'active',
        ]);

        $this->add([
            'name'     => 'theme',
            'required' => false,
        ]);

        $this->add([
            'name'     => 'layout',
            'required' => false,
        ]);

        $this->add([
            'name'     => 'template',
            'required' => false,
        ]);

        $this->add([
            'name'       => 'content',
            'filters'    => [
                [
                    'name' => 'StringTrim',
                ],
            ],
            'validators' => [
                new Validator\PageTemplateAvailable(),
            ],
        ]);

        // Check media module
        if (Pi::service('module')->isActive('media')) {

            $this->add(
                [
                    'name'     => 'main_image',
                    'required' => false,
                ]
            );

            $this->add(
                [
                    'name'     => 'additional_images',
                    'required' => false,
                ]
            );
        }

        $this->add([
            'name'     => 'id',
            'required' => false,
        ]);

        // seo_title
        $this->add([
            'name'     => 'seo_title',
            'required' => false,
        ]);

        // seo_keywords
        $this->add([
            'name'     => 'seo_keywords',
            'required' => false,
        ]);

        // seo_description
        $this->add([
            'name'     => 'seo_description',
            'required' => false,
        ]);
    }
}
