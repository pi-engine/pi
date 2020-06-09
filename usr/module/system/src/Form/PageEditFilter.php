<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 */

namespace Module\System\Form;

use Laminas\InputFilter\InputFilter;

/**
 * Page edit form filter
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class PageEditFilter extends InputFilter
{
    /**
     * Constructor
     */
    public function __construct()
    {
        $this->add([
            'name'     => 'id',
            'required' => true,
        ]);

        $this->add([
            'name'     => 'cache_type',
            'required' => false,
        ]);

        $this->add([
            'name'     => 'cache_ttl',
            'required' => false,
        ]);

        $this->add([
            'name'     => 'cache_level',
            'required' => false,
        ]);
    }
}
