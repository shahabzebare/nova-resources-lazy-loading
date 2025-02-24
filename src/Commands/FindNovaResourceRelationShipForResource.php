<?php

namespace Shahabzebare\NovaResourcesLazyLoading\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Laravel\Nova\Actions\ActionResource;
use Laravel\Nova\Events\ServingNova;
use Laravel\Nova\Fields\BelongsToMany;
use Laravel\Nova\Fields\Field;
use Laravel\Nova\Fields\HasMany;
use Laravel\Nova\Fields\HasManyThrough;
use Laravel\Nova\Fields\MorphMany;
use Laravel\Nova\Fields\MorphToMany;
use Laravel\Nova\Http\Requests\NovaRequest;
use Laravel\Nova\Nova;
use Laravel\Nova\Resource;
use ReflectionClass;
use ReflectionException;
use Shahabzebare\NovaResourcesLazyLoading\NovaLazyLoading;
use Symfony\Component\Finder\Finder;

class FindNovaResourceRelationShipForResource extends Command
{
    /**
     * @var string
     */
    protected $signature = 'sz:nova:find-resource-relationship';

    /**
     * @var string
     */
    protected $description = 'Find Nova Resource Relationship For Resource';


    /**
     * @throws ReflectionException
     */
    private function getResourceIn(): array
    {
        $namespace = app()->getNamespace();
        $resources = [];
        foreach ((new Finder())->in(app_path('Nova'))->files() as $resource) {
            $resource = $namespace.str_replace(
                    ['/', '.php'],
                    ['\\', ''],
                    Str::after($resource->getPathname(), app_path().DIRECTORY_SEPARATOR)
                );

            if (
                is_subclass_of($resource, Resource::class) &&
                ! (new ReflectionClass($resource))->isAbstract() &&
                ! (is_subclass_of($resource, ActionResource::class))
            ) {
                $resources[Str::plural(Str::kebab(class_basename($resource)))] = $resource;
            }
        }

        foreach (NovaLazyLoading::$resources as $resource) {
            $resources[Str::plural(Str::kebab(class_basename($resource)))] = $resource;
        }

        return $resources;
    }

    public function handle(): int
    {
        // Dispatch NovaRequest
        ServingNova::dispatch(new NovaRequest());

        // Get resources
        $resources = $this->getResourceIn();
        $resourceForCache = [];

        // Iterate through each resource
        foreach ($resources as $key => $resourceStr) {
            $this->info($key);
            $resource = new $resourceStr;
            $fields = $resource->fields(new NovaRequest());
            $childResource = [$resourceStr];

            // Check and add related resources
            foreach ($fields as $field) {
                if ($this->isRelationshipField($field)) {
                    $childResource[] = $field->resourceClass;
                }
            }

            // Cache the child resources
            $resourceForCache[$key] = $childResource;
        }
        
        // Write to file as JSON
        file_put_contents(storage_path('nova-relationship.json'), json_encode($resourceForCache));

        return Command::SUCCESS;
    }

    /**
     * Check if the field is a relationship field.
     *
     * @param mixed $field
     * @return bool
     */
    private function isRelationshipField(mixed $field): bool
    {
        return $field instanceof HasMany ||
            $field instanceof MorphMany ||
            $field instanceof BelongsToMany ||
            $field instanceof HasManyThrough ||
            $field instanceof MorphToMany;
    }
}
