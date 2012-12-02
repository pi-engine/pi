<?php
/**
 * Navigation menu helper
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
 * @since           3.0
 * @package         Pi\View
 * @subpackage      Helper
 * @version         $Id$
 */

namespace Pi\View\Helper\Navigation;

use Pi;
use RecursiveIteratorIterator;
use Zend\Navigation\Navigation as Container;
use Zend\View\Helper\Navigation\Menu as ZendMenu;
use Zend\Cache\Storage\Adapter\AbstractAdapter as CacheAdapter;

class Menu extends ZendMenu
{
    /**
     * Cache container
     *
     * @var StdClass
     */
    protected $cache;

    public function setCache($cache)
    {
        $this->cache = $cache;
    }

    /**
     * Renders a pair menu: normal menu and its active sub
     *
     *
     * Available $options:
     *
     *
     * @param  AbstractContainer $container [optional] container to create menu from.
     *                              Default is to use the container retrieved
     *                              from {@link getContainer()}.
     * @param  array     $options   [optional] options for controlling rendering
     *                      ulClass         CSS class for first UL for parent menu
     *                      indent          initial indentation for parent menu
     *                      minDepth        minimum depth for parent menu
     *                      maxDepth        maximum depth for parent menu
     *                      escapeLabels    to escape labels for parent menu
     *                      sub
     *                          ulClass         CSS class for first UL for sub menu
     *                          indent          initial indentation for sub menu
     *                          maxDepth        maximum depth for sub menu
     *                          escapeLabels    to escape labels for sub menu
     *
     * @return array()  array of parent menu and sub menu
     */
    public function renderPair($container = null, array $options = array())
    {
        $this->parseContainer($container);
        if (null === $container) {
            $container = $this->getContainer();
        }
        list($parent, $sub) = array('', '');
        if (!isset($options['onlyActiveBranch'])) {
            $options['onlyActiveBranch'] = true;
        }

        // find deepest active
        list($minDepth, $maxDepth) = array(0, null);
        $found = $this->findActive($container, $minDepth, $maxDepth);
        if ($found) {
            $foundPage  = $found['page'];
            $foundDepth = $found['depth'];
        } else {
            $foundPage = null;
        }

        $_this = $this;
        $_eol = static::EOL;
        $render = function ($container, $options = array(), $limitDepth = null, &$return = array()) use ($_this, $_eol, $foundPage)
        {
            extract($options);

            $html = '';
            // create iterator
            $iterator = new RecursiveIteratorIterator($container, RecursiveIteratorIterator::SELF_FIRST);
            if (is_int($limitDepth)) {
                $iterator->setMaxDepth($limitDepth);
            }

            // iterate container
            $prevDepth = -1;
            foreach ($iterator as $page) {
                $depth = $iterator->getDepth();
                if ($maxDepth && $depth > $maxDepth) {
                    continue;
                }
                $isActive = $page->isActive(true);
                if ($depth < $minDepth || !$_this->accept($page)) {
                    // page is below minDepth or not accepted by acl/visibility
                    continue;
                } elseif ($maxDepth && $depth == $maxDepth) {
                    // page is in the deepest branch
                    $accept = true;
                } elseif ($depth > $minDepth && $onlyActiveBranch && !$isActive) {
                    // page is not active itself, but might be in the active branch
                    $accept = false;
                    if ($foundPage) {
                        if ($foundPage->hasPage($page)) {
                            // accept if page is a direct child of the active page
                            $accept = true;
                        } elseif ($foundPage->getParent()->hasPage($page)) {
                            // page is a sibling of the active page...
                            if (!$foundPage->hasPages() ||
                                is_int($maxDepth) && $foundDepth + 1 > $maxDepth) {
                                // accept if active page has no children, or the
                                // children are too deep to be rendered
                                $accept = true;
                            }
                        }
                    }

                    if (!$accept) {
                        continue;
                    }
                }

                // make sure indentation is correct
                $depth -= $minDepth;
                $myIndent = $indent . str_repeat('        ', $depth);

                if ($depth > $prevDepth) {
                    // start new ul tag
                    if ($ulClass && $depth ==  0) {
                        $ulClass = ' class="' . $ulClass . '"';
                    } else {
                        $ulClass = '';
                    }
                    $html .= $myIndent . '<ul' . $ulClass . '>' . $_eol;
                } elseif ($prevDepth > $depth) {
                    // close li/ul tags until we're at current depth
                    for ($i = $prevDepth; $i > $depth; $i--) {
                        $ind = $indent . str_repeat('        ', $i);
                        $html .= $ind . '    </li>' . $_eol;
                        $html .= $ind . '</ul>' . $_eol;
                    }
                    // close previous li tag
                    $html .= $myIndent . '    </li>' . $_eol;
                } else {
                    // close previous li tag
                    $html .= $myIndent . '    </li>' . $_eol;
                }

                // render li tag and page
                $liClass = $isActive ? ' class="active"' : '';
                $html .= $myIndent . '    <li' . $liClass . '>' . $_eol
                    . $myIndent . '        ' . $_this->htmlify($page, $escapeLabels) . $_eol;

                // store as previous depth for next iteration
                $prevDepth = $depth;

                // Get sub menu
                if (is_int($maxDepth) && $depth == $maxDepth && $isActive) {
                    $return = $page;
                }
            }

            if ($html) {
                // done iterating container; close open ul/li tags
                for ($i = $prevDepth + 1; $i > 0; $i--) {
                    $myIndent = $indent . str_repeat('        ', $i-1);
                    $html .= $myIndent . '    </li>' . $_eol
                        . $myIndent . '</ul>' . $_eol;
                }
                $html = rtrim($html, $_eol);
            }

            return $html;
        };

        $options = $this->normalizeOptions($options);
        $optionsSub = isset($options['sub']) ? $options['sub'] : array();


        $limitDepth = isset($optionsSub['maxDepth']) ? $optionsSub['maxDepth'] : null;
        $subPages = array();
        $parent = $render($container, $options, $limitDepth, $subPages);
        if ($subPages) {
            $optionsSub = $this->normalizeOptions($optionsSub);
            $sub = $render($subPages, $optionsSub, $limitDepth);
        }

        return array($parent, $sub);
    }

    /**
     * Renders menu
     *
     * Implements {@link HelperInterface::render()}.
     *
     * If a partial view is registered in the helper, the menu will be rendered
     * using the given partial script. If no partial is registered, the menu
     * will be rendered as an 'ul' element by the helper's internal method.
     *
     * @see renderPartial()
     * @see renderMenu()
     *
     * @param  AbstractContainer $container [optional] container to render. Default is
     *                              to render the container registered in the
     *                              helper.
     * @return string               helper output
     */
    public function render($container = null)
    {
        Pi::service('log')->start('menu');

        // Try to load from cache
        if ($this->cache) {
            $cacheKey = $this->cache->key . '-mu';
            $content = $this->cache->storage->getItem($cacheKey);
            if (null !== $content) {
                if (Pi::service()->hasService('log')) {
                    Pi::service('log')->info('Menu is loaded from cache.');
                }
                return $content;
            }
        }

        // Generate if no cache available
        $content = parent::render($container);

        // Save to cache
        if ($this->cache) {
            $this->cache->storage->setItem($cacheKey, $content);
        }

        Pi::service('log')->end('menu');

        return $content;
    }
}