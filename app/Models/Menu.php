<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\BaseModel;
use App\Traits\Database\Slugger;
use App\Traits\Hashids\Hashids;
use App\Traits\Node\SimpleNode;
use App\Traits\Trans\Translatable;

class Menu extends BaseModel
{
    use Hashids, Slugger, Translatable, SoftDeletes, SimpleNode;

    /**
     * Configuartion for the model.
     *
     * @var array
     */
    protected $config = 'model.menu.menu';

    public function getHasRoleAttribute($value)
    {
        if (empty($this->role)) {
            return true;
        }

        if (is_array($this->role) && user()->isOne($this->role)) {
            return true;
        }

        return false;

    }

}
