<?php

namespace Shahabzebare\NovaResourcesLazyLoading\Traits;

use Cache;
use Illuminate\Support\Str;
use Laravel\Nova\Nova;

trait ResourceLazyLoading
{
    private function readJsonFileFromStorage()
    {
        return Cache::remember('nova-relationship', 60 * 60 * 24, static function () {
            $content = file_get_contents(storage_path('nova-relationship.json'));
            return json_decode($content, true);
        });

    }

    protected function resources(): void
    {
        $request = request();
        if(!Str::startsWith($request->path(), 'nova-api')) {
            $resource = $request->route()->parameters['resource'] ?? '';
            if($resource) {
                $resources = $this->readJsonFileFromStorage();
                if(array_key_exists($resource, $resources)) {
                    if($request->route()?->getName() === 'nova.pages.detail' || $request->route()?->getName() === 'nova.pages.attach') {
                        Nova::resources($resources[$resource]);
                    } else {
                        Nova::resources([$resources[$resource][0]]);
                    }
                }
            }
        } else {
            parent::resources();
        }
    }
}
