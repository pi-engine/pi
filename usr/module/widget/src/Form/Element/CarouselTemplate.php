<?php
/**
 * Form element Carousel template select class
 *
 * You may not change or alter any portion of this comment or credits
 * of supporting developers from this source code or any supporting source code
 * which is considered copyrighted (c) material of the original comment or credit authors.
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 *
 * @copyright       Copyright (c) Pi Engine http://www.xoopsengine.org
 * @license         http://www.xoopsengine.org/license New BSD License
 * @author          Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 * @package         Module\Widget
 * @subpackage      Form
 */

namespace Module\Widget\Form\Element;

use Pi;
use Zend\Form\Element\Select;

class CarouselTemplate extends Select
{
    protected function getStyles()
    {
        $styles = array(
            'carousel-bootstrap'     => __('Bootstrap slide'),
            'carousel-jcarousel'    => __('jCarousel riding Carousel'),
            'carousel-parallax'    => __('Parallax Content Slider'),
        );
        return $styles;
    }

    /**
     * @return array
     */
    public function getValueOptions()
    {
        if (empty($this->valueOptions)) {
            $this->valueOptions = $this->getStyles();
        }

        return $this->valueOptions;
    }
}
