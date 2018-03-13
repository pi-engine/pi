<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link         http://code.piengine.org for the Pi Engine source repository
 * @copyright    Copyright (c) Pi Engine http://piengine.org
 * @license      http://piengine.org/license.txt BSD 3-Clause License
 */

namespace Module\Article\Form;

use Zend\InputFilter\InputFilter;

/**
 * Validate and filer form class
 *
 * @author Zongshu Lin <lin40553024@163.com>
 */
class MediaEditFilter extends InputFilter
{
    /**
     * Initializing validator and filter
     */
    public function __construct($options = [])
    {
        $params = [
            'table' => 'media',
        ];
        if (isset($options['id']) and $options['id']) {
            $params['id'] = $options['id'];
        }
        $this->add([
            'name'       => 'name',
            'required'   => true,
            'filters'    => [
                [
                    'name' => 'StringTrim',
                ],
            ],
            'validators' => [
                [
                    'name'    => 'Module\Article\Validator\RepeatName',
                    'options' => $params,
                ],
            ],
        ]);

        $this->add([
            'name'     => 'title',
            'required' => true,
            'filters'  => [
                [
                    'name' => 'StringTrim',
                ],
            ],
        ]);

        $this->add([
            'name'     => 'description',
            'required' => false,
        ]);

        $this->add([
            'name'     => 'url',
            'required' => true,
        ]);

        $this->add([
            'name'     => 'type',
            'required' => false,
        ]);

        $this->add([
            'name'     => 'id',
            'required' => false,
        ]);
    }
}
