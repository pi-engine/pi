<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 * @package         Service
 */

namespace Pi\Application\Service;

use Pi;
use Pi\Markup\Markup as Renderer;

/**
 * Markup service for rendering content
 *
 * Note:
 *  - `newline` is enabled for Text to HTML rendering by default
 *  - `xss_filter` is enabled for HTML rendering by default
 *
 * Code sample:
 *
 * <code>
 *  // Render as HTML with full tags
 *  Pi::service('markup')->render($content, 'html');
 *
 *  // Render as HTML with specified tags
 *  Pi::service('markup')->render($content, 'html', array('tags' => '<p><div>'));
 *
 *  // Render as HTML from markdown
 *  Pi::service('markup')->render($content, 'html', 'markdown');
 *
 *  // Render as HTML from text - nb2br
 *  Pi::service('markup')->render($content, 'html', 'text');
 *
 *  // Render as HTML with XSS filtering by default
 *  Pi::service('markup')->render($content, 'html');
 *
 *  // Render as HTML with XSS filtering explicitly
 *  Pi::service('markup')->render($content, 'html', array('xss_filter' => true));
 *  Pi::service('markup')->render($content, 'html', 'markdown', array('xss_filter' => true));
 *
 *  // Render as HTML w/o XSS filtering explicitly
 *  Pi::service('markup')->render($content, 'html', array('xss_filter' => false));
 *
 *  // Render as plaintext with newline - htmlspecialchars
 *  Pi::service('markup')->render($content, 'text');
 *  Pi::service('markup')->render($content, 'text', 'text');
 *  Pi::service('markup')->render($content, 'text', array('newline' => true));
 *  Pi::service('markup')->render($content, 'text', 'text', array('newline' => true));
 *
 *  // Render as plaintext with newline from HTML - strip tags
 *  Pi::service('markup')->render($content, 'text', 'html');
 *
 *  // Render as plaintext w/o newline from markdown
 *  Pi::service('markup')->render($content, 'text', 'markdown');
 *
 *  // Render as plaintext with filters of user and tag support
 *  Pi::service('markup')->render($content, 'text', 'html', array(
 *      'filters'   => array(
 *          'user'  => array(),
 *          'tag'   => array(
 *      ),
 *  );
 * </code>
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Markup extends AbstractService
{
    /** {@inheritDoc} */
    protected $fileIdentifier = 'markup';

    /**
     * Log service constructor
     *
     * @param array $options
     */
    public function __construct($options = array())
    {
        parent::__construct($options);

        if (!empty($this->options['encoding'])) {
            Renderer::setEncoding($this->options['encoding']);
        }

        if (!empty($this->options['filters'])) {
            Renderer::setFilters($this->options['filters']);
        }
    }

    /**
     * Render content
     *
     * @param string $content   Raw content
     * @param string $renderer  Renderer type
     * @param string|array $parser String for parser or source type: `markdown`, `html`, `text`; array for options
     * @param array  $options
     *
     * @return string
     */
    public function render(
        $content,
        $renderer = null,
        $parser = null,
        $options = array()
    ) {
        return Renderer::render($content, $renderer, $parser, $options);
    }
}
