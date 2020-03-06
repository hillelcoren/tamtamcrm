<?php

namespace App\Repositories\Base;

use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Input;

class BaseRepository implements BaseRepositoryInterface
{

    protected $model;

    /**
     * BaseRepository constructor.
     * @param Model $model
     */
    public function __construct(Model $model)
    {
        $this->model = $model;
    }

    /**
     * @param array $attributes
     * @return mixed
     */
    public function create(array $attributes)
    {
        return $this->model->create($attributes);
    }

    /**
     * @param array $data
     * @return bool
     */
    public function update(array $data): bool
    {
        return $this->model->update($data);
    }

    /**
     * @param array $columns
     * @param string $orderBy
     * @param string $sortBy
     * @return mixed
     */
    public function all($columns = ['*'], string $orderBy = 'id', string $sortBy = 'asc')
    {
        return $this->model->orderBy($orderBy, $sortBy)->get($columns);
    }

    /**
     * @param string $id
     * @return mixed
     */
    public function find($id)
    {
        return $this->model->find($id);
    }

    /**
     * @param  $id
     * @return mixed
     * @throws ModelNotFoundException
     */
    public function findOneOrFail($id)
    {
        return $this->model->findOrFail($id);
    }

    /**
     * @param array $data
     * @return Collection
     */
    public function findBy(array $data)
    {
        return $this->model->where($data)->get();
    }

    /**
     * @param array $data
     * @return mixed
     */
    public function findOneBy(array $data)
    {
        return $this->model->where($data)->first();
    }

    /**
     * @param array $data
     * @return mixed
     * @throws ModelNotFoundException
     */
    public function findOneByOrFail(array $data)
    {
        return $this->model->where($data)->firstOrFail();
    }

    /**
     * @return bool
     * @throws Exception
     */
    public function delete(): bool
    {
        return $this->model->delete();
    }

    /**
     * @param $entity
     * @param $type
     *
     * @return string
     */
    private function getEventClass($entity, $type)
    {
        return 'App\Events\\' . ucfirst(class_basename($entity)) . 'Was' . $type;
    }

    /**
     * @param $entity
     */
    public function archive($entity)
    {
        if ($entity->trashed()) {
            return;
        }

        $entity->delete();

        $className = $this->getEventClass($entity, 'Archived');

        if (class_exists($className)) {
            event(new $className($entity));
        }

        return true;
    }

    /**
     * @param $entity
     */
    public function restore($entity)
    {
        if (!$entity->trashed()) {
            return;
        }
        $fromDeleted = false;
        $entity->restore();
        if ($entity->is_deleted) {
            $fromDeleted = true;
            $entity->is_deleted = false;
            $entity->save();
        }

        $className = $this->getEventClass($entity, 'Restored');

        if (class_exists($className)) {
            event(new $className($entity, $fromDeleted));
        }
    }

    /**
     * @param $entity
     */
    public function newDelete($entity)
    {
        if ($entity->is_deleted) {
            return;
        }
        $entity->is_deleted = true;
        $entity->save();
        $entity->delete();

        $className = $this->getEventClass($entity, 'Deleted');

        if (class_exists($className)) {
            event(new $className($entity));
        }

        return true;
    }

    /**
     * Paginate arrays
     *
     * @param array $data
     * @param int $perPage
     * @return LengthAwarePaginator
     */
    public function paginateArrayResults(array $data, int $perPage = 50)
    {
        $page = Input::get('page', 1);
        $offset = ($page * $perPage) - $perPage;
        return new LengthAwarePaginator(array_values(array_slice($data, $offset, $perPage, true)), count($data),
            $perPage, $page, [
                'path' => app('request')->url(),
                'query' => app('request')->query()
            ]);
    }

    public function paginateCollection($items, $perPage = 15, $options = [])
    {
        $page = Input::get('page', 1);
        //$items = $items->forPage($page, $perPage); //Filter the page var


        return new LengthAwarePaginator($items->forPage($page, $perPage), count($items) ?: $this->count(), $perPage,
            $page, [
                'path' => app('request')->url(),
                'query' => app('request')->query(),
            ]);
    }

}
