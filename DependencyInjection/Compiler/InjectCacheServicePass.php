<?php

/*
 * This file is part of the prestaSitemapPlugin package.
 * (c) David Epely <depely@prestaconcept.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Presta\SitemapBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Registering services tagged with presta.sitemap.listener as actual event listeners
 *
 * @author Konstantin Tjuterev <kostik.lv@gmail.com>
 */
class InjectCacheServicePass implements CompilerPassInterface
{
    /**
     * Adds services tagges as presta.sitemap.listener as event listeners for
     * corresponding sitemap event
     *
     * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
     *
     * @throws \InvalidArgumentException
     */
    public function process(ContainerBuilder $container)
    {
        $cacheService = $container->getParameter('presta_sitemap.cache_service');
        if (!$cacheService) {
            return;
        }

        $cacheDefinition = $container->findDefinition($cacheService);
        $cacheRefClass = new \ReflectionClass($cacheDefinition->getClass());
        $cacheInterface = 'Doctrine\Common\Cache\Cache';
        if (!$cacheRefClass->implementsInterface($cacheInterface)) {
            throw new InvalidArgumentException(sprintf(
                'Service "%s" must implement interface "%s".',
                $cacheService,
                $cacheInterface
            ));
        }

        $definition = $container->findDefinition('presta_sitemap.generator');
        $definition->addArgument(new Reference($cacheDefinition));
        $definition->addArgument($container->getParameter('presta_sitemap.timetolive'));
    }
}
