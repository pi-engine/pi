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

use Laminas\View\Helper\AbstractHtmlElement;
use InstagramScraper\Instagram as InstagramPlugin;

/**
 * Helper for loading Instagram posts
 * Usage inside a phtml template
 *
 *  // map
 *  $options = [
 *      'count'   => 12,     // number of posts
 *      'columns' => 4,      // number of columns 1,2,3,4,6
 *      'type'    => 'full', // complete or compact
 *  ];
 *
 * ```
 *  $this->instagram($username);
 *  $this->instagram($username, $options);
 * ```
 *
 * @see    https://github.com/postaddictme/instagram-php-scraper
 * @author Hossein Azizabadi Farahani <hossein@azizabadi.com>
 */
class Instagram extends AbstractHtmlElement
{
    /**
     * Loading Instagram posts
     *
     * @param string
     * @param array
     *
     * @return string
     */
    public function __invoke($username, $options = [])
    {
        // Set html template
        $htmlTemplate
            = <<<'EOT'
<div class="clearfix row" itemscope itemtype="http://schema.org/ImageGallery">
    %s
</div>
EOT;

        // Set html template
        $imageCompactTemplate
            = <<<'EOT'
<figure class="%s mb-3" itemprop="associatedMedia" itemscope itemtype="http://schema.org/ImageObject">
    <div class="card">
        <a href="%s" title="%s" target="_blank" itemprop="contentUrl">
            <img src="%s" class="card-img-top" alt="%s" itemprop="thumbnail">
        </a>
        <meta itemprop="width" content="%s">
        <meta itemprop="height" content="%s">
    </div>
</figure>
EOT;

        // Set html template
        $imageCompleteTemplate
            = <<<'EOT'
<figure class="%s mb-3" itemprop="associatedMedia" itemscope itemtype="http://schema.org/ImageObject">
    <div class="card">
        <a href="%s" title="%s" target="_blank" itemprop="contentUrl">
            <img src="%s" class="card-img-top" alt="%s" itemprop="thumbnail">
        </a>
        <meta itemprop="width" content="%s">
        <meta itemprop="height" content="%s">
        <figcaption itemprop="caption description" class="card-body">
            <div class="card-text">%s</div>
            <div class="card-text text-muted mt-2" itemscope="dateCreated">%s</div>
            <div class="card-text" itemprop="copyrightHolder">
                <a class="card-link" href="%s" title="%s" target="_blank">%s</a>
            </div>
        </figcaption>
    </div>
</figure>
EOT;

        // Set options
        $options['count']   = isset($options['count']) ? $options['count'] : 12;
        $options['columns'] = isset($options['columns']) ? $options['columns'] : 4;
        $options['type']    = isset($options['type']) ? $options['type'] : 'complete';

        // Set columns
        switch ($options['columns']) {
            case 1:
                $class = 'col-lg-12 col-md-12 col-sm-12';
                break;

            case 2:
                $class = 'col-lg-6 col-md-6 col-sm-12';
                break;

            case 3:
                $class = 'col-lg-4 col-md-4 col-sm-6';
                break;

            default:
            case 4:
                $class = 'col-lg-3 col-md-3 col-sm-6';
                break;

            case 6:
                $class = 'col-lg-2 col-md-2 col-sm-6';
                break;

        }

        // Load instagram api
        $instagram = new InstagramPlugin();
        $medias    = $instagram->getMedias($username, $options['count']);
        $account   = $instagram->getAccount($username);

        // Make image template list
        $list = [];
        foreach ($medias as $media) {
            // Get image list
            $imageList = $media->getSquareImages();
            $image     = $imageList[4];

            // Set text
            $shortText = trim(_escape(mb_substr(strip_tags($media->getCaption()), 0, 80, 'utf-8'))) . " ...";
            $fullText  = trim(_escape($media->getCaption()));

            // Set time
            $time = $this->getTimeAgo($media->getCreatedTime());

            // get image size
            [$width, $height] = getimagesize($image);

            // Get account information
            $fullName   = empty($account->getFullName()) ? $username : $account->getFullName();
            $accountUrl = sprintf('https://www.instagram.com/%s', $username);

            // Make html template
            switch ($options['type']) {
                case 'complete':
                    $list[] = sprintf(
                        $imageCompleteTemplate,
                        $class,
                        $media->getLink(),
                        $shortText,
                        $image,
                        $shortText,
                        $width,
                        $height,
                        $fullText,
                        $time,
                        $accountUrl,
                        $fullName,
                        $fullName
                    );
                    break;

                case 'compact':
                    $list[] = sprintf(
                        $imageCompactTemplate,
                        $class,
                        $media->getLink(),
                        $shortText,
                        $image,
                        $shortText,
                        $width,
                        $height
                    );
                    break;
            }
        }

        // Make full html
        $content = sprintf($htmlTemplate, implode(PHP_EOL, $list));
        return $content;
    }

    protected function getTimeAgo($ptime)
    {
        $estimate_time = time() - $ptime;

        if ($estimate_time < 1) {
            return 'less than 1 second ago';
        }

        $condition = [
            12 * 30 * 24 * 60 * 60 => 'year',
            30 * 24 * 60 * 60      => 'month',
            24 * 60 * 60           => 'day',
            60 * 60                => 'hour',
            60                     => 'minute',
            1                      => 'second',
        ];

        foreach ($condition as $secs => $str) {
            $d = $estimate_time / $secs;

            if ($d >= 1) {
                $r = round($d);
                return 'about ' . $r . ' ' . $str . ($r > 1 ? 's' : '') . ' ago';
            }
        }
    }
}
