<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Http\Request;
use App\Repositories\Eloquent\UserRepositoryInterface;
use App\Repositories\MessageRepositoryInterface;

class MessageService
{

	protected $request;

	protected $userRepository;

	protected $messageRepository;

	function __construct(Request $request,
                         UserRepositoryInterface $userRepository,
                         MessageRepositoryInterface $messageRepository
						 )
	{
		$this->request = $request;
		$this->userRepository = $userRepository;
		$this->messageRepository = $messageRepository;
	}

	/**
	 * 获取纸条列表
	 */
	public function getMessageList(array $param)
	{
		$param['uid'] = $this->userRepository->getUser()->uid;
		return $this->messageRepository->getMessageList($param);
	}


	/**
	 * 为指定的用户创建纸条
	 */
	public function SystemMessageSingleOne($user_id, $content, $type = '系统通知', $name = '系统')
	{
		$this->messageRepository->createMessageSingleOne($user_id, '1', $type, $name, $content);
		return true;
	}

	/**y
	 * 为当前用户创建纸条
	 */
	public function SystemMessageCurrentUser($content, $type = '系统通知', $name = '系统')
	{
		$this->SystemMessage2SingleOne(User::tokenAuth()->user_id, $content, $push = false, $type, $name);
		return true;
	}
}