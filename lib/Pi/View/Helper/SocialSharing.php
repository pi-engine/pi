<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 * @package         View
 */

namespace Pi\View\Helper;

use Pi;
use Zend\View\Helper\AbstractHtmlElement;

/**
 * Helper for loading `rrssb` (or ridiculously responsive social sharing buttons)
 *
 * Usage inside a phtml template
 *
 * ```
 *  // Sorted items
 *  $items = array(
 *      // Predefined items
 *      'email',
 *      'facebook',
 *      'twitter',
 *      'tumblr',
 *      'linkedin',
 *      'gplus',
 *      'pinterest',
 *      // on-fly custom items
 *      array(
 *          'identifier'    => 'weibo',
 *          'title'         => 'Weibo',
 *          'icon'          => 'fa-weibo',
 *          'url'           => 'http://weibo.com/?url=%url%&amp;title=%title%',
 *      )
 *  );
 *
 *  // Or display all buttons in default order
 *  $items = true; // or $items = null; or $items = array();
 *  $this->socialSharing($items, $pageTitle, $pageUrl);
 *  $this->socialSharing($items, $pageTitle, $pageUrl, $imageUrl);
 * ```
 *
 * @see https://github.com/kni-labs/rrssb For Ridiculously Responsive Social Sharing Buttons
 * @author Hossein Azizabadi <azizabadi@faragostaresh.com>
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class SocialSharing extends AbstractHtmlElement
{
    /**
     * Display social sharing buttons
     *
     * @todo The icon is not responsive yet
     *
     * @param string[]|true|null|   $items List of social network items
     * @param string $title Title
     * @param string $url Page URL
     * @param string $image Image url for pinterest
     *
     * @return  string
     */
    public function __invoke($items, $title, $url, $image = '')
    {
        $title = _escape($title);
        $url   = $this->view->escapeUrl($url);
        if ($image) {
            $image = $this->view->escapeUrl($image);
        }

        if (!$items) {
            $itemList = Pi::service('social_sharing')->buildItems($title, $url, $image);
        } else {
            $itemList = [];
            foreach ($items as $item) {
                $itemList[$item] = Pi::service('social_sharing')->buildItem($item, $title, $url, $image);
            }
        }
        $render = function ($item) {
            if (!$item || !isset($item['icon'])) {
                return '';
            }

            $template
                    = <<<'EOT'
<li class="rrssb-%s">
    <a title="%s" href="%s" class="popup">
        <span class="rrssb-icon"><i class="%s"></i></span>
        <span class="rrssb-text">%s</span>
   </a>
</li>
EOT;
            if($item['icon'] == 'fa-facebook') {
                $item['icon'] = 'fab fa-facebook-f';
            }

            if($item['icon'] == 'fa-twitter') {
                $item['icon'] = 'fab fa-twitter';
            }
            $button = sprintf($template, $item['identifier'], $item['title'], str_replace(' ', '%20', $item['url']), $item['icon'], $item['title']);

            return $button;
        };

        $buttons = '';
        foreach ($itemList as $key => $item) {
            $buttons .= $render($item);
        }

        // Generate
        if (!empty($buttons)) {
            // Load jQuery and css file
            $this->view->jQuery([
                'extension/rrssb.css',
            ]);
            // Load rrssb.min.js on footer
            $url = 'vendor/jquery/extension/rrssb.min.js';
            $url = Pi::service('asset')->getStaticUrl($url);
            $this->view->footScript()->appendFile($url);
            // Set content
            $content
                     = <<<'EOT'
<div class="share-container clearfix">
    <ul class="rrssb-buttons clearfix">
        %s
    </ul>
</div>
EOT;
            $content = sprintf($content, $buttons);
        } else {
            $content = '';
        }

        return $content;
    }
}
