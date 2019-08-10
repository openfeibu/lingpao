<?php

namespace App\Traits\AdminUser;

use Auth,Validator,Form,Hash,Response;
use Illuminate\Http\Request;
use App\Traits\AdminUser\Auth\Common;
/**
 * Trait for managing user profile.
 */
trait AdminUserPages
{
    use Common;

    /**
     * List apis for a particular user.
     *
     * @param Model   $user
     * @param step    next step for the workflow.
     *
     * @return Response
     */

    public function logout(Request $request)
    {
        Auth::logout(getenv('guard'));
        return redirect('/');
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function getPassword(Request $request, $role = null)
    {
        return $this->response->title('修改密码')
            ->view('user.password')
            ->output();
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function home()
    {
        return $this->response
            ->layout('user')
            ->title('Dashboard')
            ->view('home')
            ->output();
    }

    /**
     * Display the specified resource.
     *
     * @param Request $request
     * @param int     $id
     *
     * @return Response
     */
    public function postPassword(Request $request, $role = null)
    {
        $user = $request->user($this->getGuard());

        $validator = Validator::make($request->all(), [
            'old_password' => 'required',
            'password'     => 'required|confirmed|min:6',
        ],[
            'old_password.required' => '旧密码不能为空',
            'password.required' => '新密码不能为空',
            'password.confirmed' => '重复新密码不正确',
            'password.min' => '密码最少六位',
        ]);
        if ($validator->fails()) {
            return $this->response->message($validator->errors()->first())
                ->code(400)
                ->status('error')
                ->url(guard_url('password'))
                ->redirect();
        }

        if (!Hash::check($request->get('old_password'), $user->password)) {
            return $this->response->message('旧密码错误')
                ->code(400)
                ->status('error')
                ->url(guard_url('password'))
                ->redirect();
        }

        $password = $request->get('password');

        $user->password = bcrypt($password);

        if ($user->save()) {
            return $this->response->message('修改成功')
                ->code(0)
                ->status('success')
                ->url(guard_url('password'))
                ->redirect();
        } else {
            return $this->response->message('出错了')
                ->code(400)
                ->status('error')
                ->url(guard_url('password'))
                ->redirect();
            /*
            return redirect()->back()->withMessage('Error while resetting password.')->withCode(400);
            */
        }

    }

    /**
     * Show the form for creating a new resource.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function getProfile(Request $request)
    {
        $user = $request->user($this->getGuard());
        Form::populate($user);

        return $this->response->title('Profile')
            ->view('user.profile')
            ->data(compact('user'))
            ->output();
    }

    /**
     * Display the specified resource.
     *
     * @param Request $request
     *
     * @return Response
     */
    public function postProfile(Request $request)
    {
        $user = $request->user();

        $this->validate($request, [
            'name' => 'required|min:3',
        ]);

        $user->fill($request->all());

        if ($user->save()) {
            return redirect()->back()->withMessage('Profile updated successfully.')->withCode(201);
        } else {
            return redirect()->back()->withMessage('Error while updating profile.')->withCode(400);
        }

    }

    /**
     * Show locked screen.
     *
     * @return \Illuminate\Http\Response
     */
    public function locked()
    {

        return $this->response
            ->title('Locked')
            ->layout('user')
            ->view('user.locked')
            ->output();
    }

    /**
     * Show master table lists.
     *
     * @return \Illuminate\Http\Response
     */
    public function masters()
    {
        return $this->response->title('Masters')
            ->view('masters')
            ->output();
    }

    /**
     * Show reports homepage.
     *
     * @return \Illuminate\Http\Response
     */
    public function reports()
    {
        return $this->response->title('Reports')
            ->view('reports')
            ->output();
    }

}
