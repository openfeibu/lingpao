<?php

namespace App\Traits\Hashids;

trait Hashids
{

    /**
     * Get the route key for the model.
     *
     * @return string
     */
    public function findOrFail($id, $columns = ['*'])
    {
//        $id = hashids_decode($id);
//        $id = empty($id) ? 0 : $id;
        return parent::findOrFail($id, $columns);
    }

    /**
     * Get the route key for the model.
     *
     * @return string
     */
    public function findOrNew($id, $columns = ['*'])
    {
//        $id = hashids_decode($id);
//        $id = empty($id) ? 0 : $id;
        return parent::findOrNew($id, $columns);
    }

    /**
     * Get the route key for the model.
     *
     * @return string
     */
    public function getRouteKey()
    {
        return hashids_encode($this->getKey());
    }
}
