<?php

namespace Beep\Resty;

use Exception;
use Illuminate\Container\Container;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Pagination\AbstractPaginator;
use League\Fractal\Manager;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;
use League\Fractal\Resource\Collection;
use League\Fractal\Resource\Item;
use League\Fractal\TransformerAbstract;
use Illuminate\Support\Collection as IlluminateBaseCollection;

trait EnvelopesData
{
    /**
     * Envelopes data with a transformer.
     *
     * @param mixed               $data
     * @param TransformerAbstract $transformer
     * @param null|string         $resourceKey
     *
     * @return array
     */
    public function envelope($data, TransformerAbstract $transformer, ?string $resourceKey = null): array
    {
        $paginator = null;

        if ($data instanceof AbstractPaginator) {
            $paginator = $data;
            $data      = $paginator->getCollection()->all();
        }

        // Validate if the data is a nested array to determine if a collection.
        $isCollection = is_array($data) && is_array(array_first($data)) || ! empty($paginator);

        if (empty($paginator) === true && $data instanceof IlluminateBaseCollection) {
            $data         = $data->all();
            $isCollection = true;
        }

        // Build a Fractal resource.
        $resource = ! $isCollection ?
            new Item($data, $transformer, $resourceKey) : new Collection($data, $transformer, $resourceKey);

        if (! empty($paginator) && $resource instanceof Collection) {
            $resource->setPaginator(new IlluminatePaginatorAdapter($paginator));
        }

        $data       = $this->fractal()->createData($resource)->toArray();
        $normalized = [];

        // Handles serialization abnormalities.
        switch (true) {
            case (array_has($data, 'data') === true):
                $normalized = array_only($data, ['data', 'meta']);
                break;
            default:
                $normalized['data'] = $data;
        }

        return $normalized;
    }

    /**
     * Retrieves the Fractal Manager.
     *
     * @return Manager
     * @throws BindingResolutionException
     */
    private function fractal(): Manager
    {
        try {
            return Container::getInstance()->make(Manager::class);
        } catch (Exception $exception) {
            throw new BindingResolutionException;
        }
    }
}
