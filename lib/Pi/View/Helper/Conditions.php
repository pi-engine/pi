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
 * Helper for loading term and conditions bar
 *
 * Usage inside a phtml template
 *
 * ```
 *  $this->conditions();
 * ```
 *
 * @author Frédéric TISSOT <contact@espritdev.fr>
 */
class Conditions extends AbstractHtmlElement
{
    /**
     * Add a term and conditions bar
     *
     * @return  string
     */
    public function __invoke()
    {
        $content = '';

        if (Pi::service('module')->isActive('user') && Pi::service('authentication')->hasIdentity()){
            $barLabel = __("New terms and conditions are available. Please accept them.");
            $agrementLabel = __("I agree");
            $linkLabel = __("Read Term and conditions");
            $downloadUrl = Pi::url(Pi::service('view')->getHelper('url')->__invoke('user', array('module' => 'user', 'controller' => 'condition', 'action' => 'download')));
            $acceptUrl = Pi::url(Pi::service('view')->getHelper('url')->__invoke('user', array('module' => 'user', 'controller' => 'condition', 'action' => 'accept')));

            /**
             * Check if last version of terms and conditions matches with any user timeline log
             */

            if($condition = Pi::api('condition', 'user')->getLastEligibleCondition()){
                $timelineLogCollection = Pi::api('log', 'user')->getLogCollectionByUserId(Pi::user()->getId(), 'accept_conditions', null, $condition->version);

                if(!$timelineLogCollection || $timelineLogCollection->count() == 0){

                    $content = <<<HTML
    <div id="cookie-bar" class="terms-conditions-bar fixed bottom" style="z-index:110000;"><p>{$barLabel} <a href="{$downloadUrl}" class="cb-policy" target="_blank">{$linkLabel}</a> <a href="{$acceptUrl}" class="cb-enable">{$agrementLabel}</a></p></div>
    <script type="application/javascript">
        $(document).ready(function(){
            $('.terms-conditions-bar .cb-enable').click(function(e){
                e.preventDefault();
                e.stopPropagation();
                e.stopImmediatePropagation();
                
                var acceptUrl = $(this).attr('href');
                
                $.get( acceptUrl, function( data ) {
                    $('.terms-conditions-bar').remove();
                });
                                
                return false;
            });
        });
    </script>
HTML;
                }
            }
        }

        return $content;
    }
}
