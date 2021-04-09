<?php

namespace App\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;
use Illuminate\Pagination\LengthAwarePaginator;

trait ApiResponser
{
    /**
     * @param $data
     * @param $code
     * @return \Illuminate\Http\JsonResponse
     */
    private function successResponse($data, $code)
    {
        return response()->json($data, $code);
    }

    /**
     * @param $message
     * @param $code
     * @return \Illuminate\Http\JsonResponse
     */
    protected function errorResponse($message, $code)
    {
        return response()->json(['error' => $message, 'code' => $code], $code);
    }

    /**
     * @param Collection $collection
     * @param int $code
     * @return \Illuminate\Http\JsonResponse
     */
    protected function returnAll(Collection $collection, $code = 200)
    {
        if ($collection->isEmpty()) {
            return $this->successResponse(['data' => $collection], $code);
        }
        $transformer = $collection->first()->transformer;
        $collection = $this->filterData($collection, $transformer);
        $collection = $this->sortData($collection, $transformer);
        $collection = $this->paginate($collection);
        $collection = $this->transformData($collection, $transformer);
        $collection = $this->cacheResponse($collection);

        return $this->successResponse($collection, $code);
    }

    /**
     * @param Model $model
     * @param int $code
     * @return \Illuminate\Http\JsonResponse
     */
    protected function returnOne(Model $model, $code = 200)
    {
        $transformer = $model->transformer;

        $model = $this->transformData($model, $transformer);

        return $this->successResponse($model, $code);
    }

    /**
     * @param Model $model
     * @param int $code
     * @return \Illuminate\Http\JsonResponse
     */
    protected function returnMessage($message, $code = 200)
    {
        return $this->successResponse(['data' => $message], $code);
    }

    /**
     * @param $data
     * @param $transformer
     * @return array
     */
    protected function transformData($data, $transformer)
    {
        $transformation = fractal($data, $transformer);

        return $transformation->toArray();
    }

    /**
     * @param Collection $collection
     * @param $transformer
     * @return Collection|mixed
     */
    protected function sortData(Collection $collection, $transformer)
    {
        if (request()->has('sort_by')) {
            $attr = $transformer::originalAttribute(request()->sort_by);

            $collection = $collection->sortBy->{$attr};
        }

        return $collection;
    }

    /**
     * @param Collection $collection
     * @param $transformer
     * @return Collection
     */
    protected function filterData(Collection $collection, $transformer)
    {
        foreach (request()->query() as $query => $value) {
            $attr = $transformer::originalAttribute($query);

            if (isset($attr, $value)) {
                $collection = $collection->where($attr, $value);
            }
        }

        return $collection;
    }

    /**
     * @param Collection $collection
     * @return LengthAwarePaginator
     */
    protected function paginate(Collection $collection)
    {
        $rules = [
            'per_page' => 'integer|min:2|max:50',
        ];

        Validator::validate(request()->all(), $rules);

        $page = LengthAwarePaginator::resolveCurrentPage();

        $perPage = 15;
        if (request()->has('per_page')) {
            $perPage = (int) request()->per_page;
        }

        $results = $collection->slice(($page - 1) * $perPage, $perPage)->values();

        $paginated = new LengthAwarePaginator($results, $collection->count(), $perPage, $page, [
            'path' => LengthAwarePaginator::resolveCurrentPath(),
        ]);

        $paginated->appends(request()->all());

        return $paginated;
    }

    protected function cacheResponse($data)
    {
        $url = request()->url();
        $queryParams = request()->query();

        ksort($queryParams);

        $queryString = http_build_query($queryParams);

        $fullUrl = "{$url}?{$queryString}";

        return Cache::remember($fullUrl, 30/60, function () use ($data) {
            return $data;
        });
    }
}
