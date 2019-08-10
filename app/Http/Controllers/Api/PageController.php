<?php

namespace App\Http\Controllers\Api;

use App\Repositories\Eloquent\PageCategoryRepositoryInterface;
use App\Repositories\Eloquent\PageRepositoryInterface;
use App\Repositories\Eloquent\PageRecruitRepositoryInterface;
use App\Repositories\Eloquent\SettingRepositoryInterface;
use Illuminate\Http\Request;
use App\Http\Controllers\Api\BaseController;
use App\Models\Page;

class PageController extends BaseController
{
    public function __construct(PageRepositoryInterface $page,
                                PageCategoryRepositoryInterface $category_repository,
                                PageRecruitRepositoryInterface $page_recruit_repository,
                                SettingRepositoryInterface $setting_repository)
    {
        parent::__construct();
        $this->repository = $page;
        $this->category_repository = $category_repository;
        $this->page_recruit_repository = $page_recruit_repository;
        $this->setting_repository = $setting_repository;
        $this->repository
            ->pushCriteria(\App\Repositories\Criteria\PageResourceCriteria::class);
    }
    public function getPages(Request $request)
    {
        $limit = $request->input('limit',config('app.limit'));
        $category_id = $request->input('category_id');
        $recommend_type =  $request->input('recommend_type');
        $all = $request->input('all');
        if(!$category_id){
            $cate_slug = $request->input('cate_slug');
            $category = $this->category_repository->where(['slug' => $cate_slug])->first(['id','name']);
            $category_id = $category->id;
        }

        $data = $this->repository->where(['status' => 'show']);

        $childs = $this->category_repository->where(['parent_id' => $category_id])->all(['id'])->toArray();
        if($childs){
            $child_ids = array_column($childs, 'id');
            $data = $data->whereIn('category_id' , $child_ids);
        }else{
            $data = $data->where(['category_id' => $category_id]);
        }
        if($recommend_type){
            $data = $data->where(['recommend_type' => $recommend_type]);
        }
        $category = $this->category_repository->find($category_id);
        $data = $data->orderBy('order','asc')->orderBy('id','desc')
                ->setPresenter(\App\Repositories\Presenter\Api\PageListPresenter::class);

        if($all){
            $data = $data->all();
        }else{
            $data = $data->getDataTable($limit);
        }
        return [
            'code' => '200',
            'meta_title' => $category->name,
            'meta_keyword' => '',
            'meta_description' => '',
            'data' => $data['data'],
        ];
    }
    public function getPage(Request $request, $id)
    {
        $data = $this->repository
            ->setPresenter(\App\Repositories\Presenter\Api\PageShowPresenter::class)
            ->where(['status' => 'show'])
            ->find($id);
        if(!$data)
        {
            throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException('数据不存在');
        }
        $page = $data['data'];
        return response()->json([
            'code' => '200',
            'meta_title' => $page['meta_title'],
            'meta_keyword' => $page['meta_keyword'],
            'meta_description' => $page['meta_description'],
            'data' => $page,
        ]);
    }
    public function getPageSlug(Request $request,$slug)
    {
        $data = $this->repository
            ->setPresenter(\App\Repositories\Presenter\Api\PageShowPresenter::class)
            ->where(['status' => 'show','slug' => $slug])
            ->first();
        if(!$data)
        {
            throw new \Symfony\Component\HttpKernel\Exception\NotFoundHttpException('数据不存在');
        }
        $page = $data['data'];
        return response()->json([
            'code' => '200',
            'meta_title' => $page['meta_title'],
            'meta_keyword' => $page['meta_keyword'],
            'meta_description' => $page['meta_description'],
            'data' => $page,
        ]);
    }
    /**
     * @param Request $request
     * @return response
     */
    public function getRecruits(Request $request)
    {
        $data = $this->page_recruit_repository
            ->setPresenter(\App\Repositories\Presenter\Api\RecruitListPresenter::class)
            ->orderBy('order','asc')
            ->orderBy('id','asc')
            ->all();
        return response()->json([
            'code' => '200',
            'meta_title' => '招聘',
            'meta_keyword' => '',
            'meta_description' => '',
            'data' => $data['data'],
        ]);
    }
    public function getContacts()
    {
        $data = $this->setting_repository
            ->setPresenter(\App\Repositories\Presenter\Api\SettingListPresenter::class)
            ->whereIn('slug',['company_name','longitude','latitude','tel','qq','email','address'])
            ->orderBy('order','asc')
            ->orderBy('id','asc')
            ->all();
        $contacts = [];
        foreach ($data['data'] as $key => $val)
        {
            $contacts[$val['slug']] = $val['value'];
        }
        return response()->json([
            'code' => '200',
            'meta_title' => '联系方式',
            'meta_keyword' => '',
            'meta_description' => '',
            'data' => $contacts,
        ]);
    }

}
