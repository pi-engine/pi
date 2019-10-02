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
 * Helper for loading `video js` player
 *
 * Usage inside a phtml template
 *
 * $this->videojs($source);
 * $this->videojs($source, $poster);
 * $this->videojs($source, $poster, $width, $height);
 *
 * @see http://www.videojs.com/
 * @author Hossein Azizabadi <azizabadi@faragostaresh.com>
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class Videojs extends AbstractHtmlElement
{
    /**
     * Render video/audio player with video-js
     *
     * @todo The icon is not responsive yet
     *
     * @param string $source MP4 video or MP3 audio full url
     * @param string $poster Player image full url
     * @param int $width Player width
     * @param int $height Player height
     *
     * @return  string
     */
    public function __invoke($source, $poster = '', $width = '', $height = '')
    {
        // Get media extension
        $extension = pathinfo($source, PATHINFO_EXTENSION);

        // Set template
        switch ($extension) {

            case 'mp3':
                // Set player poster
                if (empty($poster)) {
                    $image = Pi::path('upload/video-js/audio.jpg');
                    if (Pi::service('file')->exists($image)) {
                        $poster = Pi::url('upload/video-js/audio.jpg');
                    } else {
                        $poster = Pi::url('static/vendor/video-js/image/audio.jpg');
                    }
                }

                // Set player width and height
                $width  = !empty($width) ? $width : 1280;
                $height = !empty($height) ? $height : 180;

                // Set html template
                $template
                    = <<<'EOT'
<audio id="%s" class="video-js vjs-default-skin" width="%d" height="%d" controls preload="none" poster="%s" data-setup='{"aspectRatio":"%s:%s"}'>
    <source src="%s" type='audio/mp3' />
</audio>
EOT;
                break;

            case 'mp4':
                // Set player poster
                if (empty($poster)) {
                    $image = Pi::path('upload/video-js/video.jpg');
                    if (Pi::service('file')->exists($image)) {
                        $poster = Pi::url('upload/video-js/video.jpg');
                    } else {
                        $poster = Pi::url('static/vendor/player/video-js/image/video.jpg');
                    }
                }

                // Set player width and height
                $width  = !empty($width) ? $width : 1280;
                $height = !empty($height) ? $height : 720;

                // Set html template
                $template
                    = <<<'EOT'
<video id="%s" class="video-js vjs-default-skin" width="%d" height="%d" controls preload="none" poster="%s" data-setup='{"aspectRatio":"%s:%s"}'>
    <source src="%s" type='video/mp4' />
</video>
EOT;
                break;

            default:
                return '';
        }

        // Load js file
        $js = 'vendor/player/video-js/video.min.js';
        $js = Pi::service('asset')->getStaticUrl($js);
        $this->view->js($js);

        // Load css file
        $css = 'vendor/player/video-js/video-js.min.css';
        $css = Pi::service('asset')->getStaticUrl($css);
        $this->view->css($css);

        // Set random unique ID
        $id = uniqid("video-js-");

        // Set final content
        $content = sprintf($template, $id, $width, $height, $poster, $width, $height, $source);

        return $content;
    }
}