<?php
/**
 * Pi Engine (http://piengine.org)
 *
 * @link            http://code.piengine.org for the Pi Engine source repository
 * @copyright       Copyright (c) Pi Engine http://piengine.org
 * @license         http://piengine.org/license.txt BSD 3-Clause License
 */

namespace Pi\Mvc\Service;

use Pi\View\Resolver\ComponentTemplate as ComponentTemplateResolver;
use Pi\View\Resolver\ModuleTemplate as ModuleTemplateResolver;
use Pi\View\Resolver\ThemeTemplate as ThemeTemplateResolver;
use Laminas\ServiceManager\FactoryInterface;
use Laminas\ServiceManager\ServiceLocatorInterface;
use Laminas\View\Resolver as ViewResolver;

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
     * Creates a Laminas\View\Resolver\AggregateResolver and attaches the template
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
        $moduleTemplateResolver    = new ModuleTemplateResolver;
        $themeTemplateResolver     = new ThemeTemplateResolver;
        $componentTemplateResolver = new ComponentTemplateResolver;
        $resolver->attach($moduleTemplateResolver);
        $resolver->attach($themeTemplateResolver);
        $resolver->attach($componentTemplateResolver);
        /**#@-*/

        $resolver->attach($serviceLocator->get('ViewTemplatePathStack'));

        return $resolver;
    }
}
