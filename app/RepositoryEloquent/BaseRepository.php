<?php

namespace App\RepositoryEloquent;

use Illuminate\Container\Container as App;
use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

abstract class BaseRepository implements BaseInterface
{
    /**
     * @var $model
     */
    protected $model;

    /**
     * @var mixed
     */
    protected $baseModel;

    /**
     * @var $app
     */
    protected $app;

    /**
     * @var array
     */
    private $where;

    /**
     * @var array
     */
    private $orWhere;

    public function __construct()
    {
        $this->app = new App();
        $this->makeModel();
    }

    abstract public function model();

    /**
     * @return Model|mixed
     * @throws BindingResolutionException
     */
    public function makeModel()
    {
        $model = $this->app->make($this->model());
        if (!$model instanceof Model) {
            throw new Exception("Class {$this->model()} must be an instance of Illuminate\\Database\\Eloquent\\Model");
        }

        return $this->model = $this->baseModel = $model;
    }

    /**
     * @param $conditions
     * @param null $operator
     * @param null $value
     * @return $this
     */
    public function where($conditions, $operator = null, $value = null)
    {
        if (func_num_args() == 2) {
            list($value, $operator) = [$operator, '='];
        }

        $this->where[] = [$conditions, $operator, $value];

        return $this;
    }

    /**
     * @param $conditions
     * @param null $operator
     * @param null $value
     * @return $this
     */
    public function orWhere($conditions, $operator = null, $value = null)
    {
        if (func_num_args() == 2) {
            list($value, $operator) = [$operator, '='];
        }

        $this->orWhere[] = [$conditions, $operator, $value];

        return $this;
    }

    /**
     * loadWhere
     */
    private function loadWhere()
    {
        if (count($this->where)) {
            foreach ($this->where as $where) {
                if (is_array($where[0])) {
                    $this->model->where($where[0]);
                } else {
                    if (count($where) == 3) {
                        $this->model->where($where[0], $where[1], $where[2]);
                    } else {
                        $this->model->where($where[0], '=', $where[1]);
                    }
                }
            }
        }
        if (count($this->orWhere)) {
            foreach ($this->orWhere as $orWhere) {
                if (is_array($orWhere[0])) {
                    $this->model->orWhere($orWhere[0]);
                } else {
                    if (count($orWhere) == 3) {
                        $this->model->orWhere($orWhere[0], $orWhere[1], $orWhere[2]);
                    } else {
                        $this->model->orWhere($orWhere[0], '=', $orWhere[1]);
                    }
                }
            }
        }
    }

    /**
     * @param $id
     * @param array $columns
     * @return mixed
     * @throws BindingResolutionException
     */
    public function find($id, $columns = ['*'])
    {
        $this->makeModel();

        return $this->model->find($id, $columns);
    }

    /**
     * @param $id
     * @return mixed return modal data
     * @throws BindingResolutionException
     */
    public function findOrFail($id)
    {
        $this->makeModel();

        return $this->model->findOrFail($id);
    }

    /**
     * create single row
     *
     * @param array $data
     * @return mixed return model data
     * @throws BindingResolutionException
     */
    public function create(array $data)
    {
        $this->makeModel();

        return $this->model->create($data);
    }

    /**
     * Insert multiple row
     *
     * @param array $data
     * @return bool return insert status
     * @throws BindingResolutionException
     */
    public function insert(array $data)
    {
        $this->makeModel();

        return $this->model->insert($data);
    }

    /**
     * @param $condition
     * @param $data
     * @return mixed return model data
     */
    public function updateOrCreate($condition, $data)
    {
        return $this->model->updateOrCreate($condition, $data);
    }

    /**
     * get data by condition
     * @param $condition
     * @return mixed
     */
    public function findByCondition($condition)
    {
        return $this->model->where($condition)->firstOrFail();
    }

    /**
     * get data by condition
     * @param $condition
     * @return mixed
     */
    public function findCondition($condition)
    {
        return $this->model->where($condition)->first();
    }

    /**
     * delete data by condition
     * @param $condition
     * @return mixed
     */
    public function deleteByCondition($condition)
    {
        return $this->model->where($condition)->delete();
    }

    /**
     * update data by condition
     * @param $condition
     * @param $data
     * @return mixed
     */
    public function updateByCondition($condition, $data)
    {
        return $this->model->where($condition)->update($data);
    }

    /**
     * @param array $data
     * @param $id
     * @return mixed
     */
    public function update(array $data, $id)
    {
        return $this->model->findOrFail($id)->update($data);
    }

    /**
     * @param $id
     * @return mixed
     * @throws BindingResolutionException
     */
    public function delete($id)
    {
        $this->makeModel();

        return $this->model->destroy($id);
    }

    /**
     * @param $id
     * @return mixed
     * @throws BindingResolutionException
     */
    public function destroy($id)
    {
        $this->model->where('id', $id)->firstOrFail()->delete();
    }

    /**
     * @param array $columns
     * @return Collection
     */
    public function get($columns = ['*']): Collection
    {
        return $this->model->get($columns);
    }

    /**
     * @return mixed
     */
    public function count()
    {
        $this->newQuery()
            ->loadWhere();

        return $this->model->count();
    }

    /**
     * @param $column
     * @param null $key
     * @return mixed
     */
    public function lists($column, $key = null)
    {
        $this->newQuery()
            ->loadWhere();

        return $this->model->lists($column, $key = null);
    }

    /**
     * @param int $perPage
     * @param array $columns
     * @param null $limit
     * @return mixed
     */
    public function paginate($perPage = 20, $columns = ['*'], $limit = null)
    {
        $this->newQuery()
            ->loadWhere();

        return $this->model->paginate($limit);
    }

    /**
     * @return mixed
     */
    public function deleteAll()
    {
        $this->newQuery()
            ->loadWhere();

        return $this->model->deleteAll();
    }

    /**
     * @param $id
     * @param array $attributes
     * @return bool|mixed
     * @throws BindingResolutionException
     */
    public function updates($id, array $attributes)
    {
        $result = $this->find($id);
        if ($result) {
            $result->update($attributes);
            return $result;
        }

        return false;
    }

    /**
     * @return $this
     */
    public function newQuery()
    {
        $this->model = $this->baseModel;
        return $this;
    }

    /**
     * @param array $select
     * @param array $conditions
     * @param string $inColumn
     * @param array $inConditions
     * @param array $relations
     * @return Collection
     */
    public function getByConditions(
        array $select = [],
        array $conditions = [],
        string $inColumn = '',
        array $inConditions = [],
        array $relations = []
    ): Collection {
        $result = $this->model;
        if ($select) {
            $result = $result->select($select);
        }

        if ($conditions) {
            $result = $result->where($conditions);
        }

        if ($relations) {
            $result = $result->with($relations);
        }
        if ($inColumn && $inConditions) {
            $result = $result->whereIn($inColumn, $inConditions);
        }

        return $result->get();
    }
}
