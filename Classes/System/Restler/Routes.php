<?php

namespace Aoe\Restler\System\Restler;

class Routes extends \Luracast\Restler\Routes
{
    /**
     * Check if a uri is handled by restler.
     *
     * Performs a preg_match after replacing the argument placeholders of the restler uris with matching
     * regular expresionns.
     *
     * @param string $uri Uri to check
     * @return bool If uri is handled by restler
     */
    public static function containsUrl(string $uri): bool
    {
        foreach (self::findAll() as $routes) {
            foreach ($routes as $route) {
                $routeMatcher = '/^' . preg_quote('/' . rtrim($route['route']['url'], '/*'), '/') . '/';

                if (is_array($route['route']['arguments'])) {
                    foreach ($route['route']['arguments'] as $argumentName => $argumentNumber) {
                        $metadataType = $route['route']['metadata']['param'][$argumentNumber];

                        $argumentReplace = '[^\/]+';

                        switch ($metadataType['type']) {
                            case 'integer':
                                $argumentReplace = '[\d]+';
                                break;
                        }

                        $routeMatcher = str_replace(
                            preg_quote('{' . $argumentName . '}', '/'),
                            $argumentReplace,
                            $routeMatcher
                        );
                    }
                }
                if (preg_match($routeMatcher, $uri) === 1) {
                    return true;
                }
            }
        }

        return false;
    }
}
