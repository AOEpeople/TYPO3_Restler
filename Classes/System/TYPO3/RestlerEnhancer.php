<?php

namespace Aoe\Restler\System\TYPO3;

use Aoe\Restler\System\Restler\Builder as RestlerBuilder;
use Luracast\Restler\Routes;
use Symfony\Component\Debug\Debug;
use TYPO3\CMS\Core\Routing\Aspect\AspectInterface;
use TYPO3\CMS\Core\Routing\Enhancer\DecoratingEnhancerInterface;
use TYPO3\CMS\Core\Routing\RouteCollection;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Utility\DebuggerUtility;

class RestlerEnhancer implements DecoratingEnhancerInterface
{
    /**
     * @var RestlerBuilder
     */
    private $restlerBuilder;

    public function __construct($configuration)
    {
        $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        $this->restlerBuilder = $objectManager->get(RestlerBuilder::class);
    }


    /**
     * Gets pattern that can be used to redecorate (undecorate)
     * a potential previously decorated route path.
     *
     * Example:
     * + route path: 'first/second.html'
     * + redecoration pattern: '(?:\.html|\.json)$'
     * -> 'first/second' might be the redecorated route path after
     *    applying the redecoration pattern to preg_match/preg_replace
     *
     * @return string regular expression pattern
     */
    public function getRoutePathRedecorationPattern(): string
    {
        return '';
    }

    /**
     * Decorates route collection to be processed during URL resolving.
     * Executed before invoking routing enhancers.
     *
     * @param RouteCollection $collection
     * @param string $routePath URL path
     */
    public function decorateForMatching(RouteCollection $collection, string $routePath): void
    {
        $restlerObj = $this->restlerBuilder->build(null);
        if ($this->isRestlerUrl('/' . $routePath)) {
            $defaultRoute = $collection->get('default');
            $defaultRoute->setPath($routePath);
            $collection->add('restler', $defaultRoute);
        }
    }

    /**
     * Decorates route collection during URL URL generation.
     * Executed before invoking routing enhancers.
     *
     * @param RouteCollection $collection
     * @param array $parameters query parameters
     */
    public function decorateForGeneration(RouteCollection $collection, array $parameters): void
    {
    }

    /**
     * @param AspectInterface[] $aspects
     */
    public function setAspects(array $aspects): void
    {
    }

    /**
     * @return AspectInterface[]
     */
    public function getAspects(): array
    {
        return [];
    }

    private function isRestlerUrl($uri) {
        foreach (Routes::findAll() as $routes) {
            foreach($routes as $route) {
                if (strpos($uri, rtrim($route["route"]["url"], '/*'))) {
                    return true;
                }
            }
        }
        return false;
    }

}