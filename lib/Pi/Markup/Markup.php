<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Pi\Markup;

use Pi;
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
    /**
     * Encoding for renderer
     * @var string
     */
    protected static $encoding = 'UTF-8';

    /**
     * Default filters, potential: user, tag
     * @var array
     */
    protected static $filters = array(
        /*
        'user'  => array(
        ),
        'tag'   => array(
        ),
        */
    );

    /**
     * Renderer
     * @var AbstractRenderer
     */
    protected static $renderer;

    /**
     * Loaded parsers
     * @var array of AbstractParser
     */
    protected static $parsers = array();

    /**
     * Loaded renderers
     * @var array of AbstractRenderer
     */
    protected static $renderers = array();

    /**
     * Disable instantiation
     */
    private function __construct() { }

    /**
     * Set encoding
     *
     * @param string $encoding
     * @return void
     */
    public static function setEncoding($encoding)
    {
        static::$encoding = $encoding;
    }

    /**
     * Set filters
     *
     * @param array $filters
     * @return void
     */
    public static function setFilters($filters = array())
    {
        static::$filters = $filters;
    }

    /**
     * Set the renderer
     *
     * @param string|AbstractRenderer $renderer
     * @param array $options
     * @return void
     */
    public static function setRenderer($renderer, $options = array())
    {
        if (!$renderer instanceof AbstractRenderer) {
            $renderer = static::loadRenderer($renderer, $options);
        }
        static::$renderer = $renderer;
    }

    /**
     * Load parser
     *
     * @param string $parser
     * @param array $options
     * @return AbstractParser
     */
    public static function loadParser($parserName, $options = array())
    {
        $parser = ucfirst($parserName);
        if (!isset(static::$parsers[$parser])) {
            $className = '%s\Markup\Parser\\' . $parser;
            $class = sprintf($className, 'Pi');
            if (!class_exists($class)) {
                $class = sprintf($className, 'Zend');
            }
            if (class_exists($class)) {
                static::$parsers[$parser] = new $class;
            } else {
                static::$parsers[$parser] = $parserName;
            }
        }
        if (static::$parsers[$parser] instanceof AbstractParser) {
            static::$parsers[$parser]->setOptions($options);
        }

        return static::$parsers[$parser];
    }

    /**
     * Load renderer
     *
     * @param string $renderer
     * @param array  $options
     * @return AbstractRenderer
     */
    public static function loadRenderer($renderer, $options = array())
    {
        $renderer = ucfirst($renderer);
        if (!isset(static::$renderers[$renderer])) {
            $className = '%s\Markup\Renderer\\' . $renderer;
            $class = sprintf($className, 'Pi');
            if (!class_exists($class)) {
                $class = sprintf($className, 'Zend');
            }
            if (!isset($options['encoding'])) {
                $options['encoding'] = static::$encoding;
            }
            if (!isset($options['filters'])) {
                $options['filters'] = static::$filters;
            }
            static::$renderers[$renderer] = new $class($options);
        } else {
            static::$renderers[$renderer]->setOptions($options);
        }

        return static::$renderers[$renderer];
    }

    /**
     * Get renderer, load Raw as default renderer if no one is set
     *
     * @return AbstractRenderer
     */
    public static function getRenderer()
    {
        if (!static::$renderer) {
            static::$renderer = static::loadRenderer('text');
        }
        return static::$renderer;
    }

    /**
     * Render content
     *
     * @param string $content   Raw content
     * @param string $renderer  Renderer type
     * @param string|null $parser
     * @param array $renderOptions
     * @return string
     */
    public static function render(
        $content,
        $renderer,
        $parser = false,
        $renderOptions = array()
    ) {
        if (!$renderer) {
            $renderer = static::getRenderer();
        } elseif (!$renderer instanceof AbstractRenderer) {
            $renderer = static::loadRenderer($renderer, $renderOptions);
        }
        if (null !== $parser) {
            $renderer->setParser($parser ? static::loadParser($parser) : '');
        }

        return $renderer->render($content);
    }
}
