<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
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
 *      'email',
 *      'facebook',
 *      'twitter',
 *      'tumblr',
 *      'linkedin',
 *      'gplus',
 *      'pinterest'
 *  );
 *  // Or display all buttons in default order
 *  $items = true; // or $items = null; or $items = array();
 *  $this->socialSharing($items, $pageTitle, $pageUrl);
 *  $this->socialSharing($items, $pageTitle, $pageUrl, $imageUrl);
 * ```
 *
 * @see https://github.com/kni-labs/rrssb For Ridiculously Responsive Social Sharing Buttons
 * @author Hossein Azizabadi <djvoltan@gmail.com>
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class SocialSharing extends AbstractHtmlElement
{
    /**
     * Display social sharing buttons
     *
     * @todo The icon is not responsive yet
     *
     * @param string[]|true|null|   $items      List of social network items
     * @param string                $title      Title
     * @param string                $url        Page URL
     * @param string                $image      Image url for pinterest
     *
     * @return  string
     */
    public function __invoke($items, $title, $url, $image = '')
    {
        $title = _escape($title);
        $url = $this->view->escapeUrl($url);
        if ($image) {
            $image = $this->view->escapeUrl($image);
        }

        $rrssbList = array('email', 'facebook', 'twitter', 'tumblr', 'linkedin', 'gplus', 'pinterest');
        $rrssbRender = function ($item) use ($title, $url, $image) {
            switch ($item) {
                case 'email':
                    $template = <<<'EOT'
<li class="rrssb-email">
    <a title="%s" href="mailto:?subject=%s;body=%s">
        <span class="rrssb-icon"><i class="fa fa-at"></i></span>
        <span class="rrssb-text">%s</span>
   </a>
</li>
EOT;
                    $button = sprintf($template, __('Email'), $title, $url, __('Email'));
                    break;

                case 'facebook':
                    $template = <<<'EOT'
<li class="rrssb-facebook">
    <a title="%s" href="https://www.facebook.com/sharer/sharer.php?u=%s" class="popup">
        <span class="rrssb-icon"><i class="fa fa-facebook"></i></span>
        <span class="rrssb-text">%s</span>
    </a>
</li>
EOT;
                    $button = sprintf($template, __('Facebook'), $url, __('Facebook'));
                    break;

                case 'twitter':
                    $template = <<<'EOT'
<li class="rrssb-twitter">
    <a title="%s" href="http://www.twitter.com/home?status=%s%s" class="popup">
        <span class="rrssb-icon"><i class="fa fa-twitter"></i></span>
        <span class="rrssb-text">%s</span>
    </a>
</li>
EOT;
                    $button = sprintf($template, __('Twitter'), $title, $url, __('Twitter'));
                    break;

                case 'tumblr':
                    $template = <<<'EOT'
<li class="rrssb-tumblr">
    <a title="%s" href="http://tumblr.com/share?s=&amp;v=3&t=%s&amp;u=%s">
        <span class="rrssb-icon"><i class="fa fa-tumblr"></i></span>
        <span class="rrssb-text">%s</span>
    </a>
</li>
EOT;
                    $button = sprintf($template, __('Tumblr'), $title, $url, __('Tumblr'));
                    break;

                case 'linkedin':
                    $template = <<<'EOT'
<li class="rrssb-linkedin">
    <a title="%s" href="http://www.linkedin.com/shareArticle?mini=true&amp;url=%s&amp;title=%s&amp;summary=%s" class="popup">
        <span class="rrssb-icon"><i class="fa fa-linkedin"></i></span>
        <span class="rrssb-text">%s</span>
    </a>
</li>
EOT;
                    $button = sprintf($template, __('Linkedin'), $url, $title, $title, __('Linkedin'));
                    break;

                case 'gplus':
                    $template = <<<'EOT'
<li class="rrssb-googleplus">
    <a title="%s" href="https://plus.google.com/share?url=%s%s" class="popup">
        <span class="rrssb-icon"><i class="fa fa-google-plus"></i></span>
        <span class="rrssb-text">%s</span>
    </a>
</li>
EOT;
                    $button = sprintf($template, __('Google +'), $title, $url, __('Google +'));
                    break;

                case 'pinterest':
                    $template = <<<'EOT'
<li class="rrssb-pinterest">
    <a title="%s" href="http://www.pinterest.com/pin/create/button/?url=%s&amp;media=%s&amp;description=%s">
        <span class="rrssb-icon"><i class="fa fa-pinterest"></i></span>
        <span class="rrssb-text">%s</span>
    </a>
</li>
EOT;
                    $button = sprintf($template, __('Pinterest'), $url, $image, $title, __('Pinterest'));
                    break;

                default:
                    $button = '';
                    break;
            }

            return $button;
        };

        $items = $items ? (array) $items: $rrssbList;
        $buttons = '';
        foreach ($items as $item) {
            $buttons .= $rrssbRender($item);
        }

        // Generagt
        if (!empty($buttons)) {
        	// Load jQuery and css file
        	$this->view->jQuery(array(
            	'extension/rrssb.css',
        	));
            // Load rrssb.min.js on footer
            $url = 'vendor/jquery/extension/rrssb.min.js';
            $url = Pi::service('asset')->getStaticUrl($url);
            $this->view->footScript()->appendFile($url);
        	// Set content
            $content = <<<'EOT'
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
