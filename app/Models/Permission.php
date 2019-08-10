<?php

namespace App\Models;

use App\Models\BaseModel;
use App\Traits\Database\Slugger;
use App\Traits\Filer\Filer;
use App\Traits\Hashids\Hashids;
use App\Traits\Repository\PresentableTrait;
use App\Traits\Trans\Translatable;
use App\Traits\Roles\PermissionHasRelations;

class Permission extends BaseModel
{
    use Filer, Hashids, Slugger, Translatable,  PresentableTrait, PermissionHasRelations;


    /**
     * Configuartion for the model.
     *
     * @var array
     */
     protected $config = 'model.roles.permission.model';

	public function getSlugIdAttribute()
	{
	    return $this->slug . '.' . $this->id;
	}

    public function getIconHtmlAttribute()
    {
        return $this->attributes['icon'] ? '<i class="layui-icon ' . $this->attributes['icon'] . '"></i>' : '';
    }

    public function getNameAttribute($value)
    {
        if(starts_with($value, '#')) {
            return head(explode('-', $value));
        }
        return $value;
    }

    public function setNameAttribute($value)
    {
        $this->attributes['name'] = ($value == '#') ? '#-' . time() : $value;
    }

    public function getSubPermissionAttribute()
    {
        return ($this->attributes['parent_id'] == 0) ? $this->where('parent_id',$this->attributes['id'])->orderBy('order', 'asc')->get() : null;
    }

}
