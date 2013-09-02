<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Pi\Filter;

use Pi;
use Zend\Filter\AbstractFilter;

/**
 * User name filter
 *
 * Transiliate specified format of user identifier into tag links:
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
    protected $options = array(
        // Tag for user identity name
        'tag'           => '%user%',
        // Pattern for user identity
        'pattern'       => '@([a-zA-Z0-9]{3,32})',
        // Direct replacement for user identity:
        // <a href="/url/to/user/%user%" title="%user%">%user%</a>
        'replacement'   => '',
        // Callback for user identity replacement if no direct replacement
        'callback'      => '',
    );

    /**
     * Constructor
     *
     * @param array $options
     */
    public function __construct($options = array())
    {
        $this->setOptions($options);
        if (empty($this->options['replacement'])
            && empty($this->options['callback'])
        ) {
            $this->options['callback'] = function ($identity) {
                $service = Pi::service('user')->bind($identity, 'identity');
                $url = $service->getUrl('profile', $identity);
                $name = $service->name;
                $service->restore();
                return sprintf('<a href="%s" title="%s">@%s</a>',
                               $url, $name, $identity);
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
        $replacement = $this->options['replacement'];
        if ($replacement) {
            $tag = $this->options['tag'];
            $callback = function ($m) use ($replacement, $tag) {
                return str_replace($tag, $m[1], $replacement);
            };
        } else {
            $func = $this->options['callback'];
            $callback = function($m) use ($func) {
                return $func($m[1]);
            };
        }
        $value = preg_replace_callback(
            '`' . $this->options['pattern'] . '`',
            $callback,
            $value
        );

        return $value;
    }
}
