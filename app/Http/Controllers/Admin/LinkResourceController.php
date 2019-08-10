<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\ResourceController as BaseController;
use App\Models\Link;
use Illuminate\Http\Request;
use App\Repositories\Eloquent\LinkRepositoryInterface;

class LinkResourceController extends BaseController
{
    public function __construct(LinkRepositoryInterface $link)
    {
        parent::__construct();
        $this->repository = $link;
        $this->repository
            ->pushCriteria(\App\Repositories\Criteria\RequestCriteria::class);
    }
    public function index(Request $request)
    {
        $limit = $request->input('limit',config('app.limit'));
        if ($this->response->typeIs('json')) {
            $data = $this->repository
                ->setPresenter(\App\Repositories\Presenter\LinkListPresenter::class)
                ->orderBy('order','asc')
                ->orderBy('id','asc')
                ->getDataTable($limit);
            return $this->response
                ->success()
                ->count($data['recordsTotal'])
                ->data($data['data'])
                ->output();
        }
        return $this->response->title(trans('app.admin.panel'))
            ->view('link.index')
            ->output();
    }
    public function create(Request $request)
    {
        $link = $this->repository->newInstance([]);

        return $this->response->title(trans('app.admin.panel'))
            ->view('link.create')
            ->data(compact('link'))
            ->output();
    }
    public function store(Request $request)
    {
        try {
            $attributes = $request->all();

            $link = $this->repository->create($attributes);

            return $this->response->message(trans('messages.success.created', ['Module' => trans('link.name')]))
                ->code(0)
                ->status('success')
                ->url(guard_url('link/' . $link->id))
                ->redirect();
        } catch (Exception $e) {
            return $this->response->message($e->getMessage())
                ->code(400)
                ->status('error')
                ->url(guard_url('link'))
                ->redirect();
        }
    }
    public function show(Request $request,Link $link)
    {
        if ($link->exists) {
            $view = 'link.show';
        } else {
            $view = 'link.create';
        }

        return $this->response->title(trans('app.view') . ' ' . trans('link.name'))
            ->data(compact('link'))
            ->view($view)
            ->output();
    }
    public function update(Request $request,Link $link)
    {
        try {
            $attributes = $request->all();

            $link->update($attributes);

            return $this->response->message(trans('messages.success.created', ['Module' => trans('link.name')]))
                ->code(0)
                ->status('success')
                ->url(guard_url('link/' . $link->id))
                ->redirect();
        } catch (Exception $e) {
            return $this->response->message($e->getMessage())
                ->code(400)
                ->status('error')
                ->url(guard_url('link/' . $link->id))
                ->redirect();
        }
    }
    public function destroy(Request $request,Link $link)
    {
        try {
            $this->repository->forceDelete([$link->id]);

            return $this->response->message(trans('messages.success.deleted', ['Module' => trans('link.name')]))
                ->status("success")
                ->http_code(202)
                ->url(guard_url('link'))
                ->redirect();

        } catch (Exception $e) {

            return $this->response->message($e->getMessage())
                ->status("error")
                ->code(400)
                ->url(guard_url('link'))
                ->redirect();
        }
    }
    public function destroyAll(Request $request)
    {
        try {
            $data = $request->all();
            $ids = $data['ids'];
            $this->repository->forceDelete($ids);

            return $this->response->message(trans('messages.success.deleted', ['Module' => trans('link.name')]))
                ->status("success")
                ->http_code(202)
                ->url(guard_url('link'))
                ->redirect();

        } catch (Exception $e) {
            return $this->response->message($e->getMessage())
                ->status("error")
                ->code(400)
                ->url(guard_url('link'))
                ->redirect();
        }
    }
}
