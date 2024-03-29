<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt New BSD License
 */

/**
 * @author Hossein Azizabadi Farahani <hossein@azizabadi.com>
 */

namespace Module\User\Form\Element;

use Pi;
use Laminas\Form\Element\Select;

class Country extends Select
{
    /**
     * @return array
     */
    public function getValueOptions()
    {
        if (empty($this->valueOptions)) {
            $this->valueOptions = Pi::api('country', 'user')->countryList();
        }
        return $this->valueOptions;
    }

    /**
     * @return array
     */
    public function getAttributes()
    {
        $this->Attributes = [
            'size'     => 1,
            'multiple' => 0,
            'class'    => 'form-control',
        ];

        return $this->Attributes;
    }
}
