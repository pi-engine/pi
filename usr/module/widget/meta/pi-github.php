<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */

return array(
    'title'         => _a('Pi Engine Updates'),
    'description'   => _a('Block to display Pi Engine new updates on GitHub'),
    'template'      => 'pi-github',
    //'render'        => 'PiGithub',
    'config'        => array(
        // text option
        'subline' => array(
            'title'         => 'Subline',
            'description'   => 'Caption for the block',
            'edit'          => 'text',
            'filter'        => 'string',
            'value'         => _a('Pi Engine Updates'),
        ),
        // GitHub organization name
        'github_org'    => array(
            'title'         => 'GitHub org name',
            'description'   => 'Organization name, required.',
            'edit'          => 'text',
            'filter'        => 'string',
            'value'         => 'pi-engine',
        ),
        // GitHub repo name
        'github_repo'    => array(
            'title'         => 'GitHub repo name',
            'description'   => 'Repo name, optional',
            'edit'          => 'text',
            'filter'        => 'string',
            'value'         => 'pi',
        ),
        // Count of items to fetch
        'limit'    => array(
            'title'         => 'Count to fetch',
            'description'   => 'Count of items to fetch, <= 30',
            'edit'          => 'text',
            'filter'        => 'int',
            'value'         => 10,
        ),
    ),
    'access'        => array(
        'guest'     => 0,
        'member'    => 1,
    ),
);
