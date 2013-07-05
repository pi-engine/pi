<?php
/**
 * Pi Engine Markup
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
 * @since           1.0
 * @package         Pi\Markup
 * @version         $Id$
 */

namespace Pi\Markup;

use Pi;
use Pi\Markup\Parser\AbstractParser;
use Pi\Markup\Renderer\AbstractRenderer;

/**
 * Renders content
 *
 * @see Pi\Application\Service\Markup
 */
class Markup
{
    /**
     * Encoding for renderer
     *
     * @var string
     */
    protected static $encoding = 'UTF-8';

    /**
     * Default filters, potential: user, tag
     *
     * @var array()
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
     * The parser
     *
     * @var ParserInterface
     */
    //protected static $parser;

    /**
     * The renderer
     *
     * @var AbstractRenderer
     */
    protected static $renderer;

    /**
     * Loaded parsers
     *
     * @var array of AbstractParser
     */
    protected static $parsers = array();

    /**
     * Loaded renderers
     *
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
     */
    public static function setEncoding($encoding)
    {
        static::$encoding = $encoding;
    }

    /**
     * Set filters
     *
     * @param array $filters
     */
    public static function setFilters($filters = array())
    {
        static::$filters = $filters;
    }

    /**
     * Set the parser
     *
     * @param string|AbstractParser $parser
     * @param array $options
     */
    public static function ____setParser($parser, $options = array())
    {
        if (!$parser instanceof AbstractParser) {
            $parser = static::loadParser($parser, $options);
        }
        static::$parser = $parser;
    }

    /**
     * Set the renderer
     *
     * @param string|AbstractRenderer $renderer
     * @param array $options
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
            $className = '%s\\Markup\\Parser\\' . $parser;
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
            $className = '%s\\Markup\\Renderer\\' . $renderer;
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
     * Get parser, load Raw as default parser if no one is set
     * @return AbstractParser
     */
    public static function ____getParser()
    {
        if (!static::$parser) {
            static::$parser = static::loadParser('raw');
        }
        return static::$parser;
    }

    /**
     * Get renderer, load Raw as default renderer if no one is set
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
     * Factory pattern
     *
     * @param  string $parser
     * @param  string $renderer
     * @param  array $parserOptions
     * @param  array $rendererOptions
     * @return AbstractRenderer
     */
    public static function ____factory($parser, $renderer = 'Html', array $parserOptions = array(), array $rendererOptions = array())
    {
        $parser     = static::loadParser($parser, $parserOptions);
        $renderer   = static::loadRenderer($renderer, $rendererOptions);
        $renderer->setParser($parser);

        return $renderer;
    }

    /**
     * Render content
     * @param string $content
     * @param string $renderer  Renderer type
     * @param string|null $parser
     * @param array $renderOptions
     * @return string
     */
    public static function render($content, $renderer, $parser = false, $renderOptions = array())
    {
        /*
        if (!$parser) {
            $parser = static::getParser();
        } elseif (!$parser instanceof AbstractParser) {
            $parser = static::loadParser($parser);
        }
        */
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
