<?php

namespace App\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\Traits\LogsActivity;
use App\Models\BaseModel;
use App\Traits\Database\Slugger;
use App\Traits\Filer\Filer;
use App\Traits\Hashids\Hashids;
use App\Traits\Trans\Translatable;

class Page extends BaseModel
{
    use Filer, SoftDeletes, Hashids, Slugger, Translatable, LogsActivity;

    /**
     * Configuartion for the model.
     *
     * @var array
     */
    protected $config = 'model.page.page';

    /**
     * Set the pages title and heading.
     *
     * @param  string  $value
     * @return void
     */
    public function setNameAttribute($value)
    {
        $this->attributes['name']        = $value;
        $this->attributes['title']       = $value;
        $this->attributes['meta_title']  = $value;
        $this->attributes['heading']     = $value;
        $this->attributes['sub_heading'] = $value;
    }

    public function category()
    {
        return $this->belongsTo('App\Models\PageCategory', 'category_id');
    }
    public function getHomeRecommendAttribute()
    {
        return $this->recommend_type == 'home' ? true : false;
    }
}
