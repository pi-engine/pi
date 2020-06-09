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
use Laminas\InputFilter\InputFilter;

/**
 * Filter and valid class for custom draft form
 *
 * @author Zongshu Lin <lin40553024@163.com>
 */
class DraftCustomFilter extends InputFilter
{
    /**
     * Initialize validator and filter
     */
    public function __construct($mode, $options = [])
    {
        $this->add([
            'name'     => 'mode',
            'required' => true,
            'filters'  => [
                [
                    'name' => 'StringTrim',
                ],
            ],
        ]);

        if (Config::FORM_MODE_CUSTOM == $mode) {
            foreach ($options['needed'] as $element) {
                $this->add([
                    'name'       => $element,
                    'required'   => true,
                    'validators' => [
                        [
                            'name' => 'Module\Article\Validator\NotEmpty',
                        ],
                    ],
                ]);
            }
        }
    }
}
