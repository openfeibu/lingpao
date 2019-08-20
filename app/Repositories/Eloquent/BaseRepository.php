<?php

namespace App\Repositories\Eloquent;

use App\Contracts\CriteriaInterface;
use App\Contracts\RepositoryCriteriaInterface;
use App\Contracts\RepositoryInterface;
use Prettus\Repository\Eloquent\BaseRepository as PrettusRepository;

/**
 * Class BaseRepository.
 */
abstract class BaseRepository extends PrettusRepository implements RepositoryInterface, RepositoryCriteriaInterface
{

    /**
     * Apply criteria in current Query
     *
     * @return $this
     */
    protected function applyCriteria()
    {

        if ($this->skipCriteria === true) {
            return $this;
        }

        $criteria = $this->getCriteria();

        if ($criteria) {

            foreach ($criteria as $c) {

                if ($c instanceof CriteriaInterface) {
                    $this->model = $c->apply($this->model, $this);
                }

            }

        }

        return $this;
    }

    /**
     * Push Criteria for filter the query
     *
     * @param $criteria
     *
     * @return $this
     * @throws \Prettus\Repository\Exceptions\RepositoryException
     */
    public function pushCriteria($criteria)
    {

        if (is_string($criteria)) {
            $criteria = new $criteria;
        }

        if (!$criteria instanceof CriteriaInterface) {
            throw new RepositoryException("Class " . get_class($criteria) . " must be an instance of Litepie\\Repository\\Contracts\\CriteriaInterface");
        }

        $this->criteria->push($criteria);
        return $this;
    }

    /**
     * Retrieve count of records.
     *
     *
     * @return mixed
     */
    public function count()
    {
        $this->applyCriteria();
        $this->applyScope();

        $results = $this->model->count();

        $this->resetModel();
        return $results;
    }

    /**
     * Find data by id or return new if not exists.
     *
     * @param $id
     * @param array $columns
     *
     * @return mixed
     */
    public function findOrNew($id, $columns = ['*'])
    {
        $this->applyCriteria();
        $this->applyScope();
        $model = $this->model->findOrNew($id, $columns);
        $this->resetModel();
        return $this->parserResult($model);
    }

    /**
     * Create a new instance of the model.
     *
     * @param array $attributes
     *
     * @return mixed
     */
    public function newInstance(array $attributes)
    {
        $model = $this->model->newInstance($attributes);
        $this->resetModel();
        return $this->parserResult($model);
    }

    /**
     * Return data for datatable
     *
     * @param $limit
     * @return     array  array.
     */
    public function getDataTable($limit = "{config('app.limit')}")
    {
        $data = $this->paginate($limit);

        $data['recordsTotal']    = $data['meta']['pagination']['total'];
        $data['recordsFiltered'] = $data['meta']['pagination']['total'];
        $data['request']         = request()->all();
        return $data;
    }

    /**
     * Return data for bootstraptable
     *
     *
     * @return     array.
     */
    public function getBootstrapTable()
    {
        $data = $this->paginate();

        $data['total'] = count($data['data']);
        $data['rows']  = $data['data'];
        $data['request'] = request()->all();
        return $data;
    }

    /**
     * Delete multiple records.
     *
     * @param      array  $ids    The identifiers
     *
     * @return     result
     */
    public function delete($ids)
    {
        return $this->model->whereIn('id', $ids)->delete();
    }

    /**
     * Permanetly delete multiple records.
     *
     * @param      array  $ids    The identifiers
     *
     * @return     result
     */
    public function purge($ids)
    {
        return $this->model->onlyTrashed()->whereIn('id', $ids)->forceDelete();
    }

    public function forceDelete($ids)
    {
        return $this->model->whereIn('id', $ids)->forceDelete();
    }

    /**
     * Restore multiple records
     *
     * @param      array  $ids    The identifiers
     *
     * @return     result  retn result for the restore
     */
    public function restore($ids)
    {
        return $this->model->onlyTrashed()->whereIn('id', $ids)->update(['deleted_at' => null]);
    }

    /**
     * Change status of the records
     *
     * @param      string  $status  The status
     * @param      array  $ids     The identifiers
     *
     * @return     result  Result for the multiple updation
     */
    public function changeStatus($status, $ids)
    {
        return $this->model->whereIn('id', $ids)->update(['status' => $status]);
    }

    /**
     * Select multiple records
     *
     * @param      array  $ids    The identifiers
     *
     * @return     Collection  Return eloquesnt collection
     */
    public function findIds($ids)
    {
        return $this->model->whereIn('id', $ids)->get();
    }

    /**
     * Find data by slug.
     *
     * @param $value
     * @param array $columns
     *
     * @return mixed
     */
    public function findBySlug($value = null, $columns = ['*'])
    {
        $this->applyCriteria();
        $this->applyScope();
        $model = $this->model->whereSlug($value)->first($columns);
        $this->resetModel();
        return $this->parserResult($model);
    }

    /**
     * Find data by slug.
     *
     * @param $value
     * @param array $columns
     *
     * @return mixed
     */
    public function toSql()
    {
        $this->applyCriteria();
        $this->applyScope();
        return $this->model->toSql();
    }
    public function where($column, $operator = null, $value = null, $boolean = 'and')
    {
        $this->model = $this->model->where($column, $operator,$value,$boolean = 'and');

        return $this;
    }
    public function whereIn($field,$values)
    {
        $this->model = $this->model->whereIn($field, $values);
        return $this;
    }
    public function limit($count=10)
    {
        $this->model = $this->model->limit($count);
        return $this;
    }
    public function updateData($attributes)
    {
        return $this->model->update($attributes);

    }
    public function join($table, $first, $operator = null, $second = null, $type = 'inner', $where = false)
    {
        $this->model = $this->model->join($table, $first, $operator, $second, $type, $where = false);
        return $this;
    }
    public function leftJoin($table, $first, $operator = null, $second = null, $type = 'inner', $where = false)
    {
        $this->model = $this->model->leftJoin($table, $first, $operator, $second, $type, $where = false);
        return $this;
    }
}
