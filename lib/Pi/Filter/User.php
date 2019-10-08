<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 */

namespace Pi\Filter;

use Pi;
use Zend\Filter\AbstractFilter;

/**
 * User name filter
 *
 * Transliterate specified format of user identifier into tag links:
 * From `@term` to
 * `<a href="<user-profile-link>/term" title="User term">@term</a>`
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class User extends AbstractFilter
{
    /**
     * Filter options
     * @var array
     */
    protected $options
        = [
            // Tag for user identity name
            'tag'         => '%user%',
            // Pattern for user identity
            'pattern'     => '@([a-zA-Z0-9]{3,32})',
            // Direct replacement for user identity:
            // <a href="/url/to/user/name/%user%" title="%user%">%user%</a>
            'replacement' => '',
            // Callback for user identity replacement if no direct replacement
            'callback'    => null,
        ];

    /**
     * Constructor
     *
     * @param array $options
     */
    public function __construct($options = [])
    {
        $this->setOptions($options);
        if (empty($this->options['replacement'])
            && empty($this->options['callback'])
        ) {
            $this->options['callback'] = function ($value) {
                $value = preg_replace_callback(
                    '`(^|\s)@([a-zA-Z0-9]{3,32})`',
                    function ($m) {
                        $url         = Pi::service('user')->getUrl(
                            'profile',
                            ['name' => $m[2]]
                        );
                        $escapedName = _escape($m[2]);
                        return sprintf(
                            '%s<a href="%s" title="%s">@%s</a>',
                            $m[1],
                            $url,
                            $escapedName,
                            $escapedName
                        );
                    },
                    $value
                );
                return $value;
            };
        }
    }

    /**
     * Transform text
     *
     * @param string $value
     * @return string
     */
    public function filter($value)
    {
        if (!empty($this->options['callback'])) {
            $value = $this->options['callback']($value);
        } else {
            $replacement = $this->options['replacement'];
            $tag         = $this->options['tag'];
            $value       = preg_replace_callback(
                '`' . $this->options['pattern'] . '`',
                function ($m) use ($replacement, $tag) {
                    return str_replace($tag, $m[1], $replacement);
                },
                $value
            );
        }

        return $value;
    }
}
