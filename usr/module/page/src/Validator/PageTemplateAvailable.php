<?php

namespace Module\Page\Validator;

use Pi;
use Laminas\Validator\AbstractValidator;

class PageTemplateAvailable extends AbstractValidator
{
    const UNAVAILABLE = 'templateUnavailable';

    public function __construct()
    {
        $this->messageTemplates = [
            self::UNAVAILABLE => _a('Template file is not available.'),
        ];

        parent::__construct();
    }

    /**
     * Template name validate
     *
     * @param  mixed $value
     * @param  array $context
     * @return boolean
     */
    public function isValid($value, $context = null)
    {
        $this->setValue($value);

        if ('phtml' == $context['markup']) {
            $file = sprintf(
                '%s/' . Pi::config('theme') . '/custom/page/%s.phtml',
                Pi::path('theme'),
                $value
            );

            /**
             * Check if theme has parent
             */
            $parentTheme = Pi::service('theme')->getParent(Pi::config('theme'));

            if (!is_readable($file) && $parentTheme) {
                $file = sprintf(
                    '%s/' . $parentTheme . '/custom/page/%s.phtml',
                    Pi::path('theme'),
                    $value
                );
            }

            if (!is_readable($file)) {
                $file = sprintf(
                    '%s/module/page/template/front/%s.phtml',
                    Pi::path('custom'),
                    $value
                );
            }

            if (!is_readable($file)) {
                $this->error(static::UNAVAILABLE);
                return false;
            }
        }


        return true;
    }
}
