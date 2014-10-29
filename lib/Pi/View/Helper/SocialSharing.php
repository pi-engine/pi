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
 * Helper for loading ridiculously responsive social sharing buttons
 *
 * Usage inside a phtml template
 *
 * ```
 *  $options = array(
 *      'social_email'      => 1,
 *      'social_facebook'   => 1,
 *      'social_twitter'    => 1,
 *      'social_tumblr'     => 1,
 *      'social_linkedin'   => 1,
 *      'social_gplus'      => 1,
 *      'social_pinterest'  => 1,
 *  );
 *  $this->$this->SocialSharing($options, $pageTitle, $pageUrl); 
 *  $this->$this->SocialSharing($options, $pageTitle, $pageUrl, $imageUrl); 
 * ```
 *
 * @see https://github.com/kni-labs/rrssb
 * @author Hossein Azizabadi <djvoltan@gmail.com>
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class SocialSharing extends AbstractHtmlElement
{
    /**
     * Add a Social Sharing share button
     * @param array     $options       List of social networks
     * @param string    $pageTitle     Title
     * @param string    $pageUrl       Page URL
     * @param string    $imageUrl      Image url
     * @return  string
     */
    public function __invoke($options, $pageTitle, $pageUrl, $imageUrl = '')
    {
        $links = '';
        
        // Set email
        if ($options['social_email']) {
            $html = <<<'EOT'
<li class="email">
    <a title="%s" href="mailto:?subject=%s;body=%s">
        <span class="icon"><i class="fa fa-at"></i></span>
        <span class="text">%s</span>
   </a>
</li>
EOT;
            $links .= sprintf($html, __('Email'), $pageTitle, $pageUrl, __('Email'));
        }

        // Set facebook
        if ($options['social_facebook']) {
            $html = <<<'EOT'
<li class="facebook">
    <a title="%s" href="https://www.facebook.com/sharer/sharer.php?u=%s" class="popup">
        <span class="icon"><i class="fa fa-facebook"></i></span>
        <span class="text">%s</span>
    </a>
</li>
EOT;
            $links .= sprintf($html, __('Facebook'), $pageUrl, __('Facebook'));
        }

        // Set twitter
        if ($options['social_twitter']) {
            $html = <<<'EOT'
<li class="twitter">
    <a title="%s" href="http://www.twitter.com/home?status=%s%s" class="popup">
        <span class="icon"><i class="fa fa-twitter"></i></span>
        <span class="text">%s</span>
    </a>
</li>
EOT;
            $links .= sprintf($html, __('Twitter'), $pageTitle, $pageUrl, __('Twitter'));
        }

        // Set tumblr
        if ($options['social_tumblr']) {
            $html = <<<'EOT'
<li class="tumblr">
    <a title="%s" href="http://tumblr.com/share?s=&amp;v=3&t=%s&amp;u=%s">
        <span class="icon"><i class="fa fa-tumblr"></i></span>
        <span class="text">%s</span>
    </a>
</li>
EOT;
            $links .= sprintf($html, __('Tumblr'), $pageTitle, $pageUrl, __('Tumblr'));
        }

        // Set linkedin
        if ($options['social_linkedin']) {
            $html = <<<'EOT'
<li class="linkedin">
    <a title="%s" href="http://www.linkedin.com/shareArticle?mini=true&amp;url=%s&amp;title=%s&amp;summary=%s" class="popup">
        <span class="icon"><i class="fa fa-linkedin"></i></span>
        <span class="text">%s</span>
    </a>
</li>
EOT;
            $links .= sprintf($html, __('Linkedin'), $pageUrl, $pageTitle, $pageTitle, __('Linkedin'));
        }

        // Set gplus
        if ($options['social_gplus']) {
            $html = <<<'EOT'
<li class="googleplus">
    <a title="%s" href="https://plus.google.com/share?url=%s%s" class="popup">
        <span class="icon"><i class="fa fa-google-plus"></i></span>
        <span class="text">%s</span>
    </a>
</li>
EOT;
            $links .= sprintf($html, __('Google +'), $pageTitle, $pageUrl, __('Google +'));
        }

        // Set pinterest
        if ($options['social_pinterest'] && !empty($imageUrl)) {
            $html = <<<'EOT'
<li class="pinterest">
    <a title="%s" href="http://www.pinterest.com/pin/create/button/?url=%s&amp;media=%s&amp;description=%s">
        <span class="icon"><i class="fa fa-pinterest"></i></span>
        <span class="text">%s</span>
    </a>
</li>
EOT;
            $links .= sprintf($html, __('Pinterest'), $pageUrl, $imageUrl, $pageTitle, __('Pinterest'));
        }

        // Generagt
        if (!empty($links)) {
        	// Set css and js
        	$this->view->jQuery(array(
            	'extension/rrssb.css',
        		'extension/rrssb.min.js',
        	));
        	// Set content
            $content = <<<'EOT'
<div class="share-container clearfix">
    <ul class="rrssb-buttons clearfix">
        %s
    </ul>
</div>
EOT;
            $content = sprintf($content, $links);
        } else {
        	$content = '';
        }
        return $content;
    }
}
