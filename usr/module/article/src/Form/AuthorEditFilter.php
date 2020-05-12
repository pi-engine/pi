<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link         http://code.piengine.org for the Pi Engine source repository
 * @copyright    Copyright (c) Pi Engine http://piengine.org
 * @license      http://piengine.org/license.txt BSD 3-Clause License
 */

namespace Module\Article\Form;

use Laminas\InputFilter\InputFilter;

/**
 * Class for verifying and filtering form
 *
 * @author Zongshu Lin <lin40553024@163.com>
 */
class AuthorEditFilter extends InputFilter
{
    /**
     * Initializing validator and filter
     */
    public function __construct()
    {
        $this->add([
            'name'     => 'name',
            'required' => true,
            'filters'  => [
                [
                    'name' => 'StringTrim',
                ],
            ],
        ]);

        $this->add([
            'name'     => 'photo',
            'required' => false,
        ]);

        $this->add([
            'name'     => 'description',
            'required' => false,
        ]);

        $this->add([
            'name'     => 'id',
            'required' => false,
        ]);

        $this->add([
            'name'     => 'fake_id',
            'required' => false,
        ]);
    }
}
