<?php

namespace Shahabzebare\NovaResourcesLazyLoading;

class NovaLazyLoading
{
    /**
     * The registered Nova Resource out of Nova Folder
     *
     * @var array<int, class-string<\Laravel\Nova\Resource>>
     */
    public static $resources = [];

    /**
     * Register the given resources.
     *
     * @param  array<int, class-string<\Laravel\Nova\Resource>>  $resources
     * @return static
     */
    public static function resources(array $resources)
    {
        static::$resources = array_unique(
            array_merge(static::$resources, $resources)
        );

        return new static();
    }


}
