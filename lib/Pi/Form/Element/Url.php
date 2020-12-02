<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 * @package         Form
 */

namespace Pi\Form\Element;

use Laminas\Form\Element\Url as LaminasUrl;

/**
 * URL element
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Url extends LaminasUrl
{
    /**
     * {@inheritDoc}
     */
    public function getInputSpecification()
    {
        $spec             = parent::getInputSpecification();
        $spec['required'] = false;

        return $spec;
    }
}