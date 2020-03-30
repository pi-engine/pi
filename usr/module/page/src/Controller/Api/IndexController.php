<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 */

namespace Module\Page\Controller\Api;

use Pi;
use Pi\Mvc\Controller\ActionController;

/**
 * @author Hossein Azizabadi <azizabadi@faragostaresh.com>
 */
class IndexController extends ActionController
{
    public function indexAction()
    {
        // Set default result
        $result = [
            'result' => false,
            'data'   => [],
            'error'  => [
                'code'        => 1,
                'message'     => __('Nothing selected'),
                'messageFlag' => false,
            ],
        ];

        // Get info from url
        $token = $this->params('token');

        // Check token
        $check = Pi::api('token', 'tools')->check($token);
        if ($check['status'] == 1) {
            // Module config
            $config = Pi::config('', $this->getModule());

            // Get page id
            $id = $this->params('id');

            // Find page
            $row = Pi::model('page', 'page')->find($id);

            // Check
            if (!empty($row) && $row->active) {
                $content = $row->content;
                $markup  = $row->markup ?: 'text';
                if ($content && 'phtml' != $markup) {
                    $content = Pi::service('markup')->compile(
                        $content,
                        $markup
                    );
                }

                // Clean html
                $content = strip_tags($content, "<b><strong><i><p><br><ul><li><ol><h1><h2><h3><h4><h5><h6>");
                $content = str_replace("<p>&nbsp;</p>", "", $content);
                $content = preg_replace('/(<[^>]+) style=".*?"/i', '$1', $content);

                // Get main image
                $mainImage = '';
                if (Pi::service('module')->isActive('media') && $row->main_image > 0) {
                    $mainImage = Pi::api('doc', 'media')->getSingleLinkData(
                        $row->main_image,
                        $config['main_image_height'],
                        $config['main_image_width']
                    );
                }

                // update clicks
                Pi::model('page', 'page')->increment('clicks', ['id' => $row->id]);

                // Save statistics
                if (Pi::service('module')->isActive('statistics')) {
                    Pi::api('log', 'statistics')->save('page', 'index', $row->id);
                }

                // Set default result
                $result = [
                    'result' => true,
                    'data'   => [
                        [
                            'title'   => $row->title,
                            'content' => $content,
                            'image'   => $mainImage,
                        ],
                    ],
                    'error'  => [
                        'code'    => 0,
                        'message' => '',
                    ],
                ];
            } else {
                // Set error
                $result['error'] = [
                    'code'    => 1,
                    'message' => __('Page not found'),
                ];
            }
        } else {
            // Set error
            $result['error'] = [
                'code'    => $check['code'],
                'message' => $check['message'],
            ];
        }

        return $result;
    }
}
