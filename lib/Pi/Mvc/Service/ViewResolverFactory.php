<?php
/**
 * Pi Engine (http://pialog.org)
 *
 * @link            http://code.pialog.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://pialog.org
 * @license         http://pialog.org/license.txt New BSD License
 */

namespace Pi\Mvc\Service;

use Pi;
use Pi\View\Resolver\ModuleTemplate as ModuleTemplateResolver;
use Pi\View\Resolver\ThemeTemplate as ThemeTemplateResolver;
use Pi\View\Resolver\ComponentTemplate as ComponentTemplateResolver;
use Zend\View\Resolver as ViewResolver;
use Zend\ServiceManager\FactoryInterface;
use Zend\ServiceManager\ServiceLocatorInterface;

/**
 * View resolver factory
 *
 * @author Taiwen Jiang <taiwenjiang@tsinghua.org.cn>
 */
class ViewResolverFactory implements FactoryInterface
{
    /**
     * Create the aggregate view resolver
     *
     * Creates a Zend\View\Resolver\AggregateResolver and attaches the template
     * map resolver and path stack resolver
     *
     * @param  ServiceLocatorInterface $serviceLocator
     * @return ViewResolverAggregateResolver
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        $resolver = new ViewResolver\AggregateResolver();
        $resolver->attach($serviceLocator->get('ViewTemplateMapResolver'));

        /**#@+
         * Replace with Pi template mechanism
         */
        $moduleTemplateResolver = new ModuleTemplateResolver;
        $themeTemplateResolver = new ThemeTemplateResolver;
        $componentTemplateResolver = new ComponentTemplateResolver;
        $resolver->attach($moduleTemplateResolver);
        $resolver->attach($themeTemplateResolver);
        $resolver->attach($componentTemplateResolver);
        /**#@-*/

        $resolver->attach($serviceLocator->get('ViewTemplatePathStack'));

        return $resolver;
    }
}
