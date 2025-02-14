<?php

declare(strict_types=1);

/*
 * This file is part of the Sonata Project package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Sonata\PageBundle\Request;

use Symfony\Component\HttpFoundation\Request;

/**
 * @final since sonata-project/page-bundle 3.26
 */
class RequestFactory
{
    private static $types = [
        'host' => Request::class,
        'host_with_path' => SiteRequest::class,
        'host_with_path_by_locale' => SiteRequest::class,
    ];

    /**
     * @param string $type
     * @param string $uri
     * @param string $method
     * @param array  $parameters
     * @param array  $cookies
     * @param array  $files
     * @param array  $server
     * @param null   $content
     *
     * @return Request|SiteRequest
     */
    public static function create($type, $uri, $method = 'GET', $parameters = [], $cookies = [], $files = [], $server = [], $content = null)
    {
        self::configureFactory($type);

        return \call_user_func_array([self::getClass($type), 'create'], [$uri, $method, $parameters, $cookies, $files, $server, $content]);
    }

    /**
     * @param string $type
     *
     * @return Request|SiteRequest
     */
    public static function createFromGlobals($type)
    {
        self::configureFactory($type);

        return \call_user_func_array([self::getClass($type), 'createFromGlobals'], []);
    }

    /**
     * @param string $type
     */
    private static function configureFactory($type)
    {
        if (!\in_array($type, ['host_with_path', 'host_with_path_by_locale'], true)) {
            return;
        }

        Request::setFactory(static function (
            array $query = [],
            array $request = [],
            array $attributes = [],
            array $cookies = [],
            array $files = [],
            array $server = [],
            $content = null
        ) {
            return new SiteRequest($query, $request, $attributes, $cookies, $files, $server, $content);
        });
    }

    /**
     * @param string $type
     *
     * @return string
     */
    private static function getClass($type)
    {
        if (!\array_key_exists($type, self::$types)) {
            throw new \RuntimeException('invalid type');
        }

        return self::$types[$type];
    }
}
