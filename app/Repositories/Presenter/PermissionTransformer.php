<?php

namespace App\Repositories\Transformers;

use League\Fractal\TransformerAbstract;

/**
 * Class PermissionTransformer
 * @package namespace App\Transformers;
 */
class PermissionTransformer extends TransformerAbstract
{

    /**
     * Transform the \Permission entity
     * @param \App\Models\Permission $permission
     *
     * @return array
     */
    public function transform(\App\Models\Permission $permission)
    {
        return [
            'id'         => (int) $permission->id,
        ];
    }
}
