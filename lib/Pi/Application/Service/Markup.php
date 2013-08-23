<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 * @package         Service
 */

namespace Pi\Application\Service;

use Pi;
use Pi\Markup\Markup as Renderer;

/**
 * Marup service for rendering content
 *
 * Code sample:
 *
 * <code>
 *  // Render as HTML with full tags
 *  Pi::service('markup')->render($content, 'html');
 *
 *  // Render as HTML with specified tags
 *  Pi::service('markup')->render($content, 'html', null,
 *      array('tags' => '<p><div>'));
 *
 *  // Render as HTML from markdown
 *  Pi::service('markup')->render($content, 'html', 'markdown');
 *
 *  // Render as plaintext with newline - htmlspecialchars
 *  Pi::service('markup')->render($content, 'text');
 *  Pi::service('markup')->render($content, 'text', 'text');
 *  Pi::service('markup')->render($content, 'text', null,
 *      array('newline' => true));
 *  Pi::service('markup')->render($content, 'text', 'text',
 *      array('newline' => true));
 *
 *  // Render as plaintext with newline from HTML - strip tags
 *  Pi::service('markup')->render($content, 'text', 'html');
 *
 *  // Render as plaintext w/o newline from markdown
 *  Pi::service('markup')->render($content, 'text', 'markdown',
 *      array('newline' => false));
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
     * @param string           $content
     * @param string           $renderer  Renderer type, valid type: html, text
     * @param bool|null|string $parser
     * @param array            $renderOptions
     *
     * @return string
     */
    public function render(
        $content,
        $renderer = 'text',
        $parser = false,
        $renderOptions = array()
    ) {
        return Renderer::render($content, $renderer, $parser, $renderOptions);
    }
}
