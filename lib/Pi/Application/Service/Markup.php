<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 * @package         Service
 */

namespace Pi\Application\Service;

use Pi\Markup\Markup as Compiler;

/**
 * Markup service for rendering content
 *
 * Code sample:
 *
 * <code>
 *  // Render HTML content as HTML with full tags
 *  Pi::service('markup')->render($content, 'html');
 *  Pi::service('markup')->render($content, 'html', 'html');
 *
 *  // Render HTML content as HTML with specified tags
 *  Pi::service('markup')->render($content, 'html', array('tags' => '<p><div>'));
 *
 *  // Render markdown content as HTML
 *  Pi::service('markup')->render($content, 'markdown');
 *
 *  // Render text content as HTML - nb2br
 *  Pi::service('markup')->render($content, 'text');
 *  Pi::service('markup')->render($content, 'text', 'html');
 *
 *  // Render HTML with XSS filtering by default
 *  Pi::service('markup')->render($content, 'html');
 *
 *  // Render HTML with XSS filtering explicitly
 *  Pi::service('markup')->render($content, 'html', array('filters' => array('xss_sanitizer' => true)));
 *
 *  // Render HTML w/o XSS filtering explicitly
 *  Pi::service('markup')->render($content, 'html', array('filters' => array('xss_sanitizer' => false)));
 *
 *  // Render plaintext with nl2br - htmlspecialchars
 *  Pi::service('markup')->render($content, 'text');
 *  Pi::service('markup')->render($content, 'text', array('nl2br' => true));
 *  Pi::service('markup')->render($content, 'text', 'html', array('nl2br' => true));
 *
 *  // Render as plaintext with newline from HTML - strip tags
 *  Pi::service('markup')->render($content, 'html', 'text');
 *
 *  // Render as plaintext w/o newline from markdown
 *  Pi::service('markup')->render($content, 'markdown', 'text');
 *
 *  // Render with filters of user and tag support
 *  Pi::service('markup')->render($content, 'text', array(
 *      'filters'   => array(
 *          'user'  => array(),
 *          'tag'   => array(),
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

    /** @var  Compiler */
    protected $compiler;

    /**
     * Get Compiler
     *
     * @return Compiler
     */
    public function getCompiler()
    {
        if (!$this->compiler instanceof Compiler) {
            $this->compiler = new Compiler;
            $this->compiler->setOptions($this->options);
        }

        return $this->compiler;
    }

    /**
     * Compile and render content
     *
     * @param string       $content  Raw content
     * @param string       $parser   Markup format of raw content: `text`, `html`, `markdown`
     * @param string|array $renderer Markup type for rendering (`html`, ``), or array for options
     * @param array        $options
     *
     * @return string
     */
    public function Compile(
        $content,
        $parser = null,
        $renderer = null,
        $options = []
    ) {
        return $this->getCompiler()->render($content, $parser, $renderer, $options);
    }

    /**
     * Render content
     *
     * @param string       $content  Raw content
     * @param string       $renderer Markup type for rendering (`html`, ``)
     * @param string|array $parser   Markup format for raw (`text`, `html`, `markdown`), or array for options
     * @param array        $options
     *
     * @return string
     * @deprecated
     */
    public function render(
        $content,
        $renderer = null,
        $parser = null,
        $options = []
    ) {
        return $this->compile($content, $parser, $renderer, $options);
    }
}
