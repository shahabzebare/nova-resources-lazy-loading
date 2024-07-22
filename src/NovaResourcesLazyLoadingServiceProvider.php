<?php

namespace Shahabzebare\NovaResourcesLazyLoading;


use Shahabzebare\NovaResourcesLazyLoading\Commands\FindNovaResourceRelationShipForResource;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class NovaResourcesLazyLoadingServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
       $package->name('nova-resources-lazy-loading')
                ->hasCommand(FindNovaResourceRelationShipForResource::class);
    }
}
