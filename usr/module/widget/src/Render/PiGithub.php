<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 */

namespace Module\Widget\Render;

use Pi;

class PiGithub
{
    public static function render($options, $module = null)
    {
        if (empty($options['github_org'])) {
            $org    = 'pi-engine';
            $repo   = 'pi';
        } else {
            $org    = $options['github_org'];
            $repo   = $options['github_repo'];
        }
        $limit      = $options['limit'] ?: 10;
        $subline    = $options['subline'] ?: _a('Updates from GitHub');

        $url = sprintf('https://github.com/%s%s', $org, $repo ? '/' . $repo : '');
        $items = static::loadGithub($org, $repo, $limit);

        $block = array(
            'subline'   => $subline,
            'url'       => $url,
            'items'     => $items,
        );

        return $block;
    }

    protected static function loadGithub($org, $repo = '', $limit = 0)
    {
        if ($repo) {
            $repoApi = sprintf(
                'https://api.github.com/repos/%s/%s/events',
                $org,
                $repo
            );
        } else {
            $repoApi = sprintf(
                'https://api.github.com/orgs/%s/events',
                $org
            );
        }
        if ($limit) {
            $repoApi .= '?page=1&per_page=' . $limit;
        }

        $assembleAction = function ($event, $repo) {
            switch ($event['type']) {
                case 'CommitCommentEvent':
                    $comment = sprintf(
                        '<a href="%s" target="_blank">comment</a>',
                        $event['payload']['comment']['html_url']
                    );
                    $segs = explode('#', $event['payload']['comment']['html_url'], 1);
                    $commitUrl = $segs[0];
                    $commit = sprintf(
                        '<a href="%s" target="_blank">#%s</a>',
                        $commitUrl,
                        substr($event['payload']['comment']['commit_id'], 0, 7)
                    );

                    $action = sprintf(
                        'created a %s on commit %s',
                        $comment,
                        $commit
                    );
                    break;

                case 'CreateEvent':
                    $action = sprintf(
                        'created a %s <a href="https://github.com/%s/tree/%s">%s</a>',
                        $event['payload']['ref_type'],
                        $event['repo']['name'],
                        $event['payload']['ref'],
                        $event['payload']['ref']
                    );
                    break;

                case 'ForkEvent':
                    $origin = sprintf(
                        '<a href="https://github.com/%s" target="_blank">%s</a>',
                        $event['repo']['name'],
                        $event['payload']['forkee']['name']
                    );
                    $forked = sprintf(
                        '<a href="%s" target="_blank">%s</a>',
                        $event['payload']['forkee']['html_url'],
                        $event['payload']['forkee']['full_name']
                    );
                    $action = sprintf('forked %s to %s', $origin, $forked);
                    break;

                case 'GollumEvent':
                    $pages = array();
                    foreach ($event['payload']['pages'] as $page) {
                        $pages[] = sprintf(
                            '%s <a href="%s" target="_blank">%s</a>',
                            $page['action'],
                            $page['html_url'],
                            _escape($page['title'])
                        );
                    }
                    $action = sprintf(
                        'worked on wiki pages: %s',
                        implode(', ', $pages)
                    );
                    break;

                case 'IssueCommentEvent':
                    $comment = sprintf(
                        '<a href="%s" target="_blank">comment</a>',
                        $event['payload']['comment']['html_url']
                    );
                    $issue = sprintf(
                        '<a href="%s" target="_blank">%s</a>',
                        $event['payload']['issue']['html_url'],
                        _escape($event['payload']['issue']['title'])
                    );
                    $action = sprintf(
                        '%s a %s on issue: %s',
                        $event['payload']['action'],
                        $comment,
                        $issue
                    );
                    break;

                case 'IssuesEvent':
                    $issue = sprintf(
                        '<a href="%s" target="_blank">%s</a>',
                        $event['payload']['issue']['html_url'],
                        _escape($event['payload']['issue']['title'])
                    );
                    $action = sprintf(
                        '%s issue: %s',
                        $event['payload']['action'],
                        $issue
                    );
                    break;

                case 'PullRequestEvent':
                    $pr = sprintf(
                        '<a href="%s" target="_blank">%s</a>',
                        $event['payload']['pull_request']['html_url'],
                        _escape($event['payload']['pull_request']['title'])
                    );
                    $action = sprintf(
                        '%s pull request: %s',
                        $event['payload']['action'],
                        $pr
                    );
                    break;

                case 'PullRequestReviewCommentEvent':
                    $comment = sprintf(
                        '<a href="%s" target="_blank">comment</a>',
                        $event['payload']['comment']['html_url']
                    );
                    $segs = explode('#', $event['payload']['comment']['html_url'], 1);
                    $prUrl = $segs[0];
                    $pr = sprintf(
                        'from commit <a href="%s" target="_blank">#%s</a>',
                        $prUrl,
                        $event['payload']['comment']['commit_id']
                    );
                    $action = sprintf(
                        'created a %s on a pull request %s',
                        $comment,
                        $pr
                    );
                    break;

                case 'PushEvent':
                    $commits = array();
                    foreach ($event['payload']['commits'] as $item) {
                        $commits[] = sprintf(
                            '<a href="https://github.com/%s/commit/%s" target="_blank">#%s</a>',
                            $event['repo']['name'],
                            $item['sha'],
                            substr($item['sha'], 0, 7)
                        );
                    }
                    $segs = explode('/', $event['payload']['ref']);
                    $ref = $segs[2];
                    $branch = sprintf(
                        '<a href="https://github.com/%s/tree/%s">%s</a>',
                        $event['repo']['name'],
                        $ref,
                        $ref
                    );
                    $action = sprintf(
                        'pushed commits to %s: %s',
                        $branch,
                        implode(', ', $commits)
                    );
                    break;

                case 'ReleaseEvent':
                    $release = sprintf(
                        '<a href="%s" target="_blank">%s</a>',
                        $event['payload']['release']['html_url'],
                        $event['payload']['release']['rag_name']
                    );
                    $action = sprintf(
                        '%s release %s',
                        $event['payload']['action'] ?: 'published',
                        $release
                    );
                    break;

                default:
                    $action = 'did something not recognized';
                    break;
            }
            if ($repo) {
                $action .= ' [' . $repo . ']';
            }

            return $action;
        };
        $assembleEvent = function ($event) use ($assembleAction, $org, $repo) {
            $time = _date($event['created_at']);
            $actor = sprintf(
                '<a href="https://github.com/%s" target="_blank">@%s</a>',
                $event['actor']['login'],
                $event['actor']['login']
            );
            $avatar = sprintf(
                '<img src="%s%s&size=16" width="16px" alt="%s">',
                $event['actor']['avatar_url'],
                $event['actor']['gravatar_id'],
                $event['actor']['login']
            );
            $actor = sprintf('%s %s', $avatar, $actor);
            if (!$repo && !empty($event['repo'])) {
                $repo = sprintf(
                    '<a href="https://github.com/%s" target="_blank">%s</a>',
                    $event['repo']['name'],
                    $event['repo']['name']
                );
            } else {
                $repo = '';
            }
            $result = array(
                'time'      => $time,
                'actor'     => $actor,
                'action'    => $assembleAction($event, $repo),
            );

            return $result;
        };

        $result = array();
        $events = Pi::service('remote')->get($repoApi) ?: array();
        $count  = 0;
        foreach ($events as $event) {
            $result[] = $assembleEvent($event);
            $count++;
            if ($limit && $count == $limit) {
                break;
            }
        }

        return $result;
    }
}
