<?php

namespace Aoe\Restler\System\TYPO3;

use Aoe\Restler\System\RestlerBuilderAware;
use TYPO3\CMS\Core\Routing\Aspect\AspectInterface;
use TYPO3\CMS\Core\Routing\Enhancer\DecoratingEnhancerInterface;
use TYPO3\CMS\Core\Routing\RouteCollection;

/**
 * Puts the URLs which should be handled by restler into the current route collection
 * and maps them to the current default route.
 *
 * Please be aware that this seems to have some side effects on other DecoratingEnhancers like PageTypeDecorator
 * routeEnhancers:
 *   RestlerEnhancer:
 *     type: Restler
 *     default: '.json'
 */
class RestlerEnhancer extends RestlerBuilderAware implements DecoratingEnhancerInterface
{
    private $default;

    public function __construct($configuration)
    {
        $default = $configuration['default'] ?? '';

        if (!is_string($default)) {
            throw new \InvalidArgumentException('default must be string', 1538327508);
        }

        $this->default = $default;
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
        return preg_quote($this->default, '#') . '$';
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
        // set path according to typo3/sysext/core/Classes/Routing/PageRouter.php:132
        $prefixedUrlPath = '/' . trim($routePath, '/');

        if ($this->isRestlerPrefix($prefixedUrlPath)) {
            $this->getRestlerBuilder()->build(null);

            if ($this->isRestlerUrl($prefixedUrlPath)) {
                $defaultRoute = $collection->get('default');
                $defaultRoute->setPath($prefixedUrlPath);
                $collection->add('restler', $defaultRoute);
            }
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

    private function isRestlerUrl($uri): bool
    {
        return \Aoe\Restler\System\Restler\Routes::containsUrl($uri);
    }
}
