<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 */

namespace Pi\Markup;

//use Pi;
use Pi\Markup\Parser\AbstractParser;
use Pi\Markup\Renderer\AbstractRenderer;

/**
 * Markup handler
 *
 * Renders content
 *
 * @see Pi\Application\Service\Markup
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Markup
{
    /** @var array  */
    protected $options = array();

    /**
     * Encoding for renderer
     * @var string
     */
    protected $encoding = 'UTF-8';

    /**
     * Default global filters, potential: user, tag
     * @var array
     */
    protected $filters = array();

    /** @var AbstractParser[] */
    protected $parsers = array();

    /** @var AbstractRenderer[] */
    protected $renderers = array();

    /**
     * Renderer
     * @var AbstractRenderer
     */
    protected $renderer;

    /**
     * Set rendering options
     *
     * @param array $options
     *
     * @return $this
     */
    public function setOptions($options = array())
    {
        $this->options = $options;

        return $this;
    }

    /**
     * Set encoding
     *
     * @param string $encoding
     *
     * @return $this
     */
    public function setEncoding($encoding)
    {
        $this->encoding = $encoding;

        return $this;
    }

    /**
     * Set filters
     *
     * @param array $filters
     *
     * @return $this
     */
    public function setFilters($filters = array())
    {
        $this->filters = $filters;

        return $this;
    }

    /**
     * Set the renderer
     *
     * @param string|AbstractRenderer $renderer
     * @param array $options
     *
     * @return $this
     */
    public function setRenderer($renderer, $options = array())
    {
        if (!$renderer instanceof AbstractRenderer) {
            $renderer = $this->loadRenderer($renderer, $options);
        }
        $this->renderer = $renderer;

        return $this;
    }

    /**
     * Load parser
     *
     * @param string $parserName
     * @param array $options
     *
     * @return AbstractParser
     */
    public function loadParser($parserName, $options = array())
    {
        $parser = ucfirst($parserName);
        if (!isset($this->parsers[$parser])) {
            if (isset($this->options['parser'][$parserName])) {
                $options = array_merge($this->options['parser'][$parserName], $options);
            }
            $class = 'Pi\Markup\Parser\\' . $parser;
            $this->parsers[$parser] = new $class;
        }
        $this->parsers[$parser]->setOptions($options);

        return $this->parsers[$parser];
    }

    /**
     * Load renderer
     *
     * @param string $rendererName
     * @param array  $options
     *
     * @return AbstractRenderer
     */
    public function loadRenderer($rendererName = '', $options = array())
    {
        $rendererName = $rendererName ?: 'html';
        $renderer = ucfirst($rendererName);
        if (!isset($this->renderers[$renderer])) {
            if (isset($this->options['renderer'][$rendererName])) {
                $options = array_merge($this->options['renderer'][$rendererName], $options);
            }
            $class = 'Pi\Markup\Renderer\\' . $renderer;
            $this->renderers[$renderer] = new $class;
        }
        $this->renderers[$renderer]->setOptions($options);

        return $this->renderers[$renderer];
    }

    /**
     * Get renderer, load Raw as default renderer if no one is set
     *
     * @param array  $options
     *
     * @return AbstractRenderer
     */
    public function getRenderer($options = array())
    {
        if (!$this->renderer) {
            $this->renderer = $this->loadRenderer();
        }
        if ($options) {
            $this->renderer->setOptions($options);
        }

        return $this->renderer;
    }

    /**
     * Render content
     *
     * @param string $content   Raw content
     * @param string $parser    Markup format of raw content: `text`, `html`, `markdown`
     * @param string|array $renderer Markup type for rendering (`html`, ``), or array for options
     * @param array  $options
     *
     * @return string
     */
    public function render(
        $content,
        $parser     = null,
        $renderer   = null,
        $options    = array()
    ) {
        if (is_array($renderer)) {
            $options = $renderer;
            $renderer = null;
        }
        if (!$parser) {
            $parser = $this->loadParser('text');
        } elseif (!$parser instanceof AbstractParser) {
            $parser = $this->loadParser($parser);
        }
        if (!empty($options['filters'])) {
            $parser->addFilters($options['filters']);
        }
        if (!$renderer) {
            $renderer = $this->getRenderer();
        } elseif (!$renderer instanceof AbstractRenderer) {
            $renderer = $this->loadRenderer($renderer);
        }
        if (isset($options['renderer'])) {
            $renderer->setOptions($options['renderer']);
        }
        $content = $renderer->setParser($parser)->render($content);

        return $content;
    }
}
