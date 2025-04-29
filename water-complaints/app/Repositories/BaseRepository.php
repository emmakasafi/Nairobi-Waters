<?php

namespace App\Repositories;

use Illuminate\Container\Container as Application;
use Illuminate\Database\Eloquent\Model;

abstract class BaseRepository
{
    /** @var Application */
    protected $app;

    /** @var Model */
    protected $model;

    /**
     * BaseRepository constructor.
     *
     * @param Application $app
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
        $this->makeModel();
    }

    /**
     * Specify Model class name.
     *
     * @return string
     */
    abstract public function model();

    /**
     * Make Model instance.
     *
     * @return Model
     *
     * @throws \Exception
     */
    public function makeModel()
    {
        $model = $this->app->make($this->model());

        if (! $model instanceof Model) {
            throw new \Exception("Class {$this->model()} must be an instance of Illuminate\\Database\\Eloquent\\Model");
        }

        return $this->model = $model;
    }

    /**
     * Get all data from repository
     *
     * @param array $columns
     * @return \Illuminate\Support\Collection
     */
    public function all($columns = ['*'])
    {
        return $this->model->all($columns);
    }

    /**
     * Paginate for all
     */
    public function paginate($perPage = 15, $columns = ['*'])
    {
        return $this->model->paginate($perPage, $columns);
    }

    /**
     * Find data by id
     */
    public function find($id, $columns = ['*'])
    {
        return $this->model->find($id, $columns);
    }

    /**
     * Find data by field and value
     */
    public function findByField($field, $value, $columns = ['*'])
    {
        return $this->model->where($field, $value)->get($columns);
    }

    /**
     * Create new entry
     */
    public function create(array $attributes)
    {
        return $this->model->create($attributes);
    }

    /**
     * Update entry
     */
    public function update(array $attributes, $id)
    {
        $record = $this->find($id);
        return $record->update($attributes);
    }

    /**
     * Delete entry
     */
    public function delete($id)
    {
        return $this->model->destroy($id);
    }
}
