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
 * Helper for loading video player
 *
 * Usage inside a phtml template
 *
 * // Set player options
 * $option = [
 *     'type'     => 'hls',
 *     'mimetype' => 'application/x-mpegURL',
 *     'source'   => [
 *         [
 *             'url'   => 'https://localhost/stream_hls_low.m3u8',
 *             'title' => 'Low Quality',
 *         ],
 *         [
 *             'url'   => 'https://localhost/stream_hls_medium.m3u8',
 *             'title' => 'Medium Quality',
 *         ],
 *         [
 *             'url'   => 'https://localhost/stream_hls_high.m3u8',
 *             'title' => 'High Quality',
 *         ],
 *     ],
 *     'subtitle' => [
 *         [
 *            'label'   => 'English',
 *             'srclang' => 'en',
 *             'src'     => 'https://localhost/en.src',
 *         ],
 *         [
 *             'label'   => 'Persian',
 *             'srclang' => 'fa',
 *             'src'     => 'https://localhost/fa.src',
 *         ],
 *     ],
 *     'layout'   => [
 *         'title'             => 'Video title', // title / false
 *         'posterImage'       => 'https://localhost/poster.jpg', // url / false
 *         'autoPlay'          => true, // true / false
 *         'playButtonShowing' => true, // true / false
 *         'allowDownload'       => false, // true / false
 *         'allowTheatre'        => false, // true / false
 *         'playbackRateEnabled' => false, // true / false
 *         'logo'       => [
 *            'imageUrl'      => 'https://localhost/logo.png', // url / null
 *            'imagePosition' => 'top left',
 *         ],
 *         'persistent' => [
 *             'volume'  => true, // true / false
 *             'quality' => true, // true / false
 *             'speed'   => true, // true / false
 *             'theatre' => true, // true / false
 *         ],
 *         'captions'   => [
 *             'play'           => __('Play'),
 *             'pause'          => __('Pause'),
 *             'mute'           => __('Mute'),
 *             'unmute'         => __('Unmute'),
 *             'fullscreen'     => __('Fullscreen'),
 *             'exitFullscreen' => __('Exit Fullscreen'),
 *         ],
 *     ],
 * ];
 *
 * $this->video($option);
 *
 * @author Hossein Azizabadi <djvoltan@gmail.com>
 */
class Video extends AbstractHtmlElement
{
    /**
     * Render video player
     *
     * @param array $option
     *
     * @return  string
     */
    public function __invoke($option)
    {
        // Set template
        $template
            = <<<'EOT'
<video id="%s" class="%s">
    %s
</video>
EOT;

        // Set random unique ID
        $id = uniqid("video-");

        // Canonize option
        $option = $this->canonizeOption($option);

        // Canonize source
        $source = $this->canonizeSource($option);

        // Canonize layout
        $subtitle = $this->canonizeSubtitle($option);

        // Canonize layout
        $player = $this->canonizePlayer($option);

        // Load js file
        $js = Pi::service('asset')->getStaticUrl('vendor/player/fluid-player/fluidplayer.js');
        $this->view->js($js);

        // Load css file
        $css = Pi::service('asset')->getStaticUrl('vendor/player/fluid-player/fluidplayer.css');
        $this->view->css($css);

        // Set js config
        $jsScript = sprintf('fluidPlayer("%s", %s);', $id, $player);
        $this->view->footScript()->appendScript($jsScript);

        // Set final content
        $content = sprintf($template, $id, $option['class'], $source, $subtitle);

        return $content;
    }

    public function canonizeOption($option)
    {
        // Check type
        $option['type'] = isset($option['type']) && !empty($option['type']) ? $option['type'] : 'mp4';

        // Set html class
        $option['class'] = isset($option['class']) ? 'pi-player' . $option['class'] : 'pi-player';

        // Set mime type
        if (!isset($option['mimetype']) || empty($option['mimetype'])) {
            switch ($option['type']) {
                case 'mp4':
                    $option['mimetype'] = 'video/mp4';
                    break;

                case 'hls':
                    $option['mimetype'] = 'application/vnd.apple.mpegurl';
                    break;

                case 'dash':
                    $option['mimetype'] = 'application/dash+xml';
                    break;
            }
        }

        return $option;
    }

    public function canonizeSource($option)
    {
        // Add source files to template
        $sourceTemplate = [];
        foreach ($option['source'] as $source) {
            $sourceTemplate[] = sprintf('<source src="%s" title="%s" type="%s" />', $source['url'], $source['title'], $option['mimetype']);
        }

        return implode(PHP_EOL, $sourceTemplate);
    }

    public function canonizeSubtitle($option)
    {
        if (isset($option['subtitle']) && !empty($option['subtitle'])) {
            $subtitleTemplate = [];
            foreach ($option['subtitle'] as $subtitle) {
                $subtitleTemplate[] = sprintf(
                    '<track label="%s" kind="metadata" srclang="%s" src="%s" />',
                    $subtitle['label'],
                    $subtitle['srclang'],
                    $subtitle['src']
                );
            }

            return implode(PHP_EOL, $subtitleTemplate);
        }

        return '';
    }

    public function canonizePlayer($option)
    {
        $player = [
            'layoutControls' => [
                'fillToContainer'     => true,
                'primaryColor'        => false,
                'posterImage'         => isset($option['layout']['posterImage']) ? $option['layout']['posterImage'] : false,
                'autoPlay'            => isset($option['layout']['autoPlay']) ? $option['layout']['autoPlay'] : true,
                'playButtonShowing'   => isset($option['layout']['playButtonShowing']) ? $option['layout']['playButtonShowing'] : true,
                'playPauseAnimation'  => true,
                'preload'             => true,
                'mute'                => false,
                'subtitlesEnabled'    => (isset($option['subtitle']) && !empty($option['subtitle'])) ? true : false,
                'title'               => isset($option['layout']['title']) ? $option['layout']['title'] : false,
                'logo'                => [
                    'imageUrl'          => isset($option['layout']['logo']['imageUrl']) ? $option['layout']['logo']['imageUrl'] : null,
                    'position'          => isset($option['layout']['logo']['imagePosition']) ? $option['layout']['logo']['imagePosition'] : 'top left',
                    'clickUrl'          => null,
                    'opacity'           => 1,
                    'mouseOverImageUrl' => null,
                    'imageMargin'       => '2px',
                    'hideWithControls'  => false,
                    'showOverAds'       => false,
                ],
                'htmlOnPauseBlock'    => [
                    'html'   => null,
                    'height' => null,
                    'width'  => null,
                ],
                'persistentSettings'  => [
                    'volume'  => isset($option['layout']['persistent']['volume']) ? $option['layout']['persistent']['volume'] : true,
                    'quality' => isset($option['layout']['persistent']['quality']) ? $option['layout']['persistent']['quality'] : true,
                    'speed'   => isset($option['layout']['persistent']['speed']) ? $option['layout']['persistent']['speed'] : true,
                    'theatre' => isset($option['layout']['persistent']['theatre']) ? $option['layout']['persistent']['theatre'] : true,
                ],
                'allowDownload'       => isset($option['layout']['allowDownload']) ? $option['layout']['allowDownload'] : false,
                'allowTheatre'        => isset($option['layout']['allowTheatre']) ? $option['layout']['allowTheatre'] : false,
                'playbackRateEnabled' => isset($option['layout']['playbackRateEnabled']) ? $option['layout']['playbackRateEnabled'] : false,
                'controlBar'          => [
                    'autoHide'        => true,
                    'autoHideTimeout' => 3,
                    'animated'        => true,
                ],
                'captions'            => [
                    'play'           => isset($option['layout']['captions']['play']) ? $option['layout']['captions']['play'] : 'Play',
                    'pause'          => isset($option['layout']['captions']['pause']) ? $option['layout']['captions']['pause'] : 'Pause',
                    'mute'           => isset($option['layout']['captions']['mute']) ? $option['layout']['captions']['mute'] : 'Mute',
                    'unmute'         => isset($option['layout']['captions']['unmute']) ? $option['layout']['captions']['unmute'] : 'Unmute',
                    'fullscreen'     => isset($option['layout']['captions']['fullscreen']) ? $option['layout']['captions']['fullscreen'] : 'Fullscreen',
                    'exitFullscreen' => isset($option['layout']['captions']['exitFullscreen']) ? $option['layout']['captions']['exitFullscreen'] : 'Exit',
                ],
            ],
            'vastOptions'    => [],
        ];


        return json_encode($player);
    }
}