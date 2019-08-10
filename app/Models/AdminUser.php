<?php

namespace App\Models;

use Hash;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;
use App\Models\Auth as AuthModel;
use App\Traits\Database\Slugger;
use App\Traits\Database\DateFormatter;
use App\Traits\Filer\Filer;
use App\Traits\Hashids\Hashids;
use App\Traits\Repository\PresentableTrait;
use App\Traits\Roles\HasRoleAndPermission;
use App\Contracts\AdminUserPolicy;
use App\Traits\AdminUser\AdminUser as AdminUserProfile;

class AdminUser extends AuthModel implements AdminUserPolicy
{
    use Filer, Notifiable, HasRoleAndPermission, AdminUserProfile, SoftDeletes, Hashids, Slugger, PresentableTrait, DateFormatter;

    /**
     * Configuartion for the model.
     *
     * @var array
     */
    protected $config = 'model.user.admin.model';

    /**
     * Initialiaze page modal.
     *
     * @var attributes
     */

    public function __construct($attributes = [])
    {
        $config = config($this->config);

        foreach ($config as $key => $val) {

            if (property_exists(get_called_class(), $key)) {
                $this->$key = $val;
            }

        }

        parent::__construct($attributes);
    }

    public function getDobAttribute($val)
    {

        if ($val == '0000-00-00' || empty($val)) {
            return '';
        }

        return format_date(($val));
    }


    public function setPasswordAttribute($val)
    {

        if (Hash::needsRehash($val)) {
            $this->attributes['password'] = bcrypt($val);
        } else {
            $this->attributes['password'] = ($val);
        }

    }

}
