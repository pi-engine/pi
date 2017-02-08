<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt BSD 3-Clause License
 * @package         Service
 */

namespace Pi\Application\Service;

use Pi;

/**
 * Notification service
 *
 * - Pi::service('notification')->send($to, $template, $information, $module, $uid);
 * - Pi::service('notification')->smsToUser($content, $number);
 * - Pi::service('notification')->smsToAdmin($content, $number);
 * - Pi::service('notification')->cron();
 *
 * @author Hossein Azizabadi <azizabadi@faragostaresh.com>
 */
class Notification extends AbstractService
{
    /**
     * Is notification service available
     *
     * @return bool
     */
    public function active()
    {
        return Pi::service('module')->isActive('notification');
    }

    /**
     * Send mail and message notification
     *
     * @param array|string $to
     * @param string       $template
     * @param array        $information
     * @param string       $module
     * @param int          $uid
     *
     * @return array|string
     */
    public function send($to, $template, $information, $module, $uid = 0)
    {
        // Set template
        $data = Pi::service('mail')->template(
            array(
                'file'      => $template,
                'module'    => $module,
            ),
            $information
        );

        // Set message
        $message = Pi::service('mail')->message(
            $data['subject'],
            $data['body'],
            $data['format']
        );
        $message->addTo($to);
        //$message->setEncoding("UTF-8");

        // Set as notification
        if (Pi::service('module')->isActive('message') && $uid > 0) {
            Pi::api('api', 'message')->notify(
                $uid,
                $data['body'],
                $data['subject']
            );
        }

        // Send mail
        try {
            return Pi::service('mail')->send($message);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get list of active modules
     *
     * @param string       $content
     * @param string       $number
     *
     * @return bool
     */
    public function smsToUser($content, $number = '')
    {
        if (!$this->active()) {
            return;
        }

        return Pi::api('sms', 'notification')->sendTouser($content, $number);
    }

    /**
     * Get list of active modules
     *
     * @param string       $content
     * @param string       $number
     *
     * @return bool
     */
    public function smsToAdmin($content, $number = '')
    {
        if (!$this->active()) {
            return;
        }

        return Pi::api('sms', 'notification')->sendToAdmin($content, $number);
    }

    /**
     * Do cron
     *
     * @return array
     */
    public function cron()
    {
        // Set log
        Pi::service('audit')->log('cron', '==========================================');
        Pi::service('audit')->log('cron', 'Start cron system');
        // Set module list
        $moduleList = $this->moduleList();
        // Check all modules
        foreach ($moduleList as $module) {
            if (Pi::service('module')->isActive(strtolower($module))) {
                $class = sprintf('Module\%s\Api\Notification', ucfirst(strtolower($module)));
                if (class_exists($class)) {
                    if (method_exists($class, 'doCron')) {
                        Pi::api('notification', strtolower($module))->doCron();
                    }
                }
            }
        }
        // Set log
        Pi::service('audit')->log('cron', 'End cron system');
        Pi::service('audit')->log('cron', '==========================================');
    }

    /**
     * Get list of active modules
     *
     * @return array
     */
    public function moduleList()
    {
        $moduleList = array();
        $modules = Pi::registry('modulelist')->read('active');
        foreach ($modules as $module) {
            $moduleList[] = $module['name'];
        }

        return $moduleList;
    }
}