<?php
namespace Module\User;

class ActivityTest
{
    protected $module;

    public function __construct($module)
    {
        $this->module = $module;
    }

    public function get($uid, $limit, $offset = 0)
    {
        $data = array();
        for ($i = 0; $i < $limit; $i++) {
            $message = sprintf('Uid %d activity  %d items', $uid, $i);
            $data['items'][] = array(
                'message' => $message,
                'time'    => time() - $i * 3600,
            );
        }
        $data['link'] = '/user/activity/more/uid/' . $uid;

        return $data;

    }
}