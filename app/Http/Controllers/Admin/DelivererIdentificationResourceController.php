<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\ResourceController as BaseController;
use App\Models\DelivererIdentification;
use App\Services\MessageService;
use Auth;
use Illuminate\Http\Request;
use App\Http\Requests\UserRequest;
use App\Models\User;
use App\Repositories\Eloquent\DelivererIdentificationRepositoryInterface;

/**
 * Resource controller class for user.
 */
class DelivererIdentificationResourceController extends BaseController
{

    /**
     * Initialize user resource controller.
     *
     * @param type DelivererIdentificationRepositoryInterface $delivererIdentificationRepository
     */

    public function __construct(
        DelivererIdentificationRepositoryInterface $delivererIdentificationRepository
    )
    {
        parent::__construct();
        $this->repository = $delivererIdentificationRepository;
        $this->repository
            ->pushCriteria(\App\Repositories\Criteria\RequestCriteria::class);
    }
    public function index(Request $request)
    {
        $limit = $request->input('limit',config('app.limit'));

        if ($this->response->typeIs('json')) {
            $deliverer_identifications = $this->repository->join('users','users.id','=','deliverer_identifications.user_id');

            $deliverer_identifications = $deliverer_identifications
                ->selectRaw('deliverer_identifications.*,CASE deliverer_identifications.status WHEN "checking" THEN 1 ELSE 2 END as status_num,users.nickname,users.avatar_url,users.phone')
                ->orderBy('status','asc')
                ->orderBy('id','desc')
                ->paginate($limit,['deliverer_identifications.*','users.nickname','users.avatar_url','users.phone']);
            foreach ($deliverer_identifications as $key => $identification)
            {
                $identification->student_id_card_image_full = $identification->student_id_card_image_full ;
                $identification->status_desc = $identification->status_desc ;
            }
            return $this->response
                ->success()
                ->count($deliverer_identifications->total())
                ->data($deliverer_identifications->toArray()['data'])
                ->output();
        }
        return $this->response->title(trans('app.name'))
            ->view('deliverer_identification.index')
            ->output();
    }
    public function show(Request $request,DelivererIdentification $deliverer_identification)
    {
        return $this->response->title(trans('app.name'))
            ->data(compact('deliverer_identification'))
            ->view('deliverer_identification.show')
            ->output();
    }
    public function changeStatus(Request $request)
    {
        try {
            $data = $request->all();
            $id = $data['id'];

            $identification = $this->repository->find($id);
            if($identification->status != 'checking')
            {
                return $this->response->message("请勿重复提交审核")
                    ->status("error")
                    ->code(400)
                    ->url()
                    ->redirect();
            }

            DelivererIdentification::where('id',$id)->update([
                'status' => $data['status'],
                'content' => $data['content'],
            ]);
            if($data['status'] == 'passed')
            {
                User::where('id','user_id')->update(['role' => 'deliverer']);
            }

            //消息推送 发单人
            $message_data = [
                'type' => 'check',
                'content' => trans('deliverer_identification.message.'.$data['status']),
            ];
            app(MessageService::class)->sendMessage($message_data);

            return $this->response->message('审核成功')
                ->status("success")
                ->code(0)
                ->url(guard_url('deliverer_identification'))
                ->redirect();

        } catch (Exception $e) {
            return $this->response->message($e->getMessage())
                ->status("error")
                ->code(400)
                ->url(guard_url('deliverer_identification'))
                ->redirect();
        }

    }
}