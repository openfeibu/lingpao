<?php

namespace App\Repositories\Eloquent;

use Route,Auth;
use Illuminate\Support\Collection;
use App\Repositories\Eloquent\PermissionRepositoryInterface;
use App\Repositories\Eloquent\BaseRepository;


class PermissionRepository extends BaseRepository implements PermissionRepositoryInterface
{


    public function boot()
    {
        $this->fieldSearchable = config('model.roles.permission.model.search');

    }

    /**
     * Specify Model class name.
     *
     * @return string
     */
    public function model()
    {
        return config('model.roles.permission.model.model');
    }


    /**
     * Returns all users with given role.
     *
     * @return mixed
     */
    public function groupedPermissions($grouped = false)
    {
        $result = $this->model->orderBy('slug')->get()->pluck('id', 'slug')->toArray();

        $array = [];

        foreach ($result as $key => $value) {
            $key                      = explode('.', $key, 4);
            @$array[$key[0]][$key[1]][$key[2]] = $value;
        }
        return $array;
    }


    /**
     * Create a new permission using the given name.
     *
     * @param string $name
     * @param string $slug
     *
     * @throws PermissionExistsException
     *
     * @return Permission
     */
    public function createPermission($name, $slug = null)
    {

        if (!is_null($this->findByName($name))) {
            throw new PermissionExistsException('The permission ' . $name . ' already exists'); // TODO: add translation support
        }

        // Do we have a display_name set?
        $slug = is_null($slug) ? $name : $slug;

        return $permission = $this->model->create([
            'name' => $name,
            'slug' => $slug,
        ]);
    }

    /**
     * @param array $rolesIds
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getByRoles(array $rolesIds)
    {
        return $this->model->whereHas('roles', function ($query) use ($rolesIds) {
            $query->whereIn('id', $rolesIds);
        })->get();
    }

    /**
     * @param $user
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getActivesByUser($user)
    {
        $table = $user->permissions()->getTable();

        return $user->permissions()
            ->where($table . '.value', true)
            ->where(function ($q) use ($table) {
                $q->where($table . '.expires', '>=', Carbon::now());
                $q->orWhereNull($table . '.expires');
            })
            ->get();
    }
    /**
     * Permission Menus
     * @return array
     */
    public function menus()
    {
        $menus = [];
        $father = Auth::user()->menus();

        if($father) {
            foreach ($father as $item) {
                $active = ($item->slug == Route::currentRouteName()) ? true : false;
                $sub = Auth::user()->menus($item->id);

                if(!$sub->isEmpty())
                {
                    foreach ($sub as $key => $sub_item)
                    {
                        $sub_item->active = $sub_item->slug == Route::currentRouteName() ? true : false;
                        $active ? true : $active  = $sub_item->active;
                    }
                    $item->sub = $sub;
                }

                $item->active = $active;
                $menus[] = $item;
            }
        }

        return $menus;
    }
    public function permissions($parent_id = 0)
    {
        return $this->model->where('parent_id', $parent_id)->orderBy('order', 'asc')->orderBy('id', 'asc')->get();
    }

    public function allPermissions()
    {
        $permissions = collect();
        $father = $this->permissions();
        foreach ($father as $key => $item)
        {
            $sub = $this->permissions($item->id);
            $sub->isEmpty() ? '' : $item->sub = $sub;
            $permissions[$item->id] = $item;
        }
        return $permissions;
    }
}
