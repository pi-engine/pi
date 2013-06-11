<?php
/**
 * Pi Engine Markup sevice
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
 * @package         Pi\Application
 * @subpackage      Service
 * @since           3.0
 * @version         $Id$
 */

/**
 * Renders content
 *
 * Code sample:
 * <code>
 *  // Render as HTML with full tags
 *  Pi::service('markup')->render($content, 'html');
 *
 *  // Render as HTML with specified tags
 *  Pi::service('markup')->render($content, 'html', null, array('tags' => '<p><div>'));
 *
 *  // Render as HTML from markdown
 *  Pi::service('markup')->render($content, 'html', 'markdown');
 *
 *  // Render as plaintext with newline - htmlspecialchars
 *  Pi::service('markup')->render($content, 'text');
 *  Pi::service('markup')->render($content, 'text', 'text');
 *  Pi::service('markup')->render($content, 'text', null, array('newline' => true));
 *  Pi::service('markup')->render($content, 'text', 'text', array('newline' => true));
 *
 *  // Render as plaintext with newline from HTML - strip tags
 *  Pi::service('markup')->render($content, 'text', 'html');
 *
 *  // Render as plaintext w/o newline from markdown
 *  Pi::service('markup')->render($content, 'text', 'markdown', array('newline' => false));
 *
 *  // Render as plaintext w/o newline, with filters of user and tag support
 *  Pi::service('markup')->render($content, 'text', 'html', array(
 *      'newline'   => false,
 *      'filters'   => array(
 *          'user'  => array(),
 *          'tag'   => array(
 *          ),
 *      ),
 *  );
 * </code>
 */

namespace Pi\Application\Service;

use Pi;
use Pi\Markup\Markup as Renderer;

class Markup extends AbstractService
{
    protected $fileIdentifier = 'markup';

    /**
     * Log service constructor
     *
     * @param array $options
     */
    public function __construct($options = array())
    {
        parent::__construct($options);

        if (isset($this->options['encoding'])) {
            Renderer::setEncoding($this->options['encoding']);
        }

        if (isset($this->options['filters'])) {
            Renderer::setFilters($this->options['filters']);
        }
    }

    /**
     * Render content
     * @param string $content
     * @param string $renderer  Renderer type, valid type: html, text
     * @param string|null $parser
     * @param array $renderOptions
     * @return string
     */
    public function render($content, $renderer = 'text', $parser = false, $renderOptions = array())
    {
        return Renderer::render($content, $renderer, $parser, $renderOptions);
    }
}
