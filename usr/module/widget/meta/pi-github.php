<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 */

return [
    'title'       => _a('Pi Engine Updates'),
    'description' => _a('Block to display Pi Engine new updates on GitHub'),
    'template'    => 'pi-github',
    //'render'        => 'PiGithub',
    'config'      => [
        // text option
        'subline'     => [
            'title'       => 'Subline',
            'description' => 'Caption for the block',
            'edit'        => 'text',
            'filter'      => 'string',
            'value'       => _a('Pi Engine Updates'),
        ],
        // GitHub organization name
        'github_org'  => [
            'title'       => 'GitHub org name',
            'description' => 'Organization name, required.',
            'edit'        => 'text',
            'filter'      => 'string',
            'value'       => 'pi-engine',
        ],
        // GitHub repo name
        'github_repo' => [
            'title'       => 'GitHub repo name',
            'description' => 'Repo name, optional',
            'edit'        => 'text',
            'filter'      => 'string',
            'value'       => 'pi',
        ],
        // Count of items to fetch
        'limit'       => [
            'title'       => 'Count to fetch',
            'description' => 'Count of items to fetch, <= 30',
            'edit'        => 'text',
            'filter'      => 'int',
            'value'       => 10,
        ],
    ],
    'access'      => [
        'guest'  => 0,
        'member' => 1,
    ],
];
