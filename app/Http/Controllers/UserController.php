<?php

namespace App\Http\Controllers;
use App\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
class UserController extends Controller
{
    private $salt;
    public function __construct()
    {
        $this->salt = "userloginregister";
    }



    //登录
    public function login(Request $request)
    {
        if($request->has('username') && $request->has('password')){
            $user = User::where('username', '=', $request->input('username'))->where('password', '=', sha1($this->salt.$request->input('password')))->first();
            if($user){
                $token = str_random(60);
                $user->api_token = $token;
                $user->save();

                $extends['api_token'] = $user->api_token;
                return $this->Success("登录成功", $extends);
            }else{
                return $this->Error('用户名或密码不正确,登录失败');
            }
        }else{
            return $this->Error('登录信息不完整,请输入用户名和密码');
        }
    }
    //注册
    public function register(Request $request)
    {
        if($request->has('username') && $request->has('password') && $request->has('email')){
            $userModel = User::getUserByName($request->input('username'));
            if ($userModel) {
                return $this->Error("用户已存在");
            }

            $userEmailModel = User::getUserByEmail($request->input('email'));
            if ($userEmailModel) {
                return $this->Error("email已存在");
            }

            $user = new User;
            $user->username = $request->input('username');
            $user->password = sha1($this->salt.$request->input('password'));
            $user->email = $request->input('email');
            $user->api_token = str_random(60);
            if($user->save()){
                $extends['api_token'] = $user->api_token;
                return $this->Success("注册成功", $extends);
            }else{
                return $this->Error('用户注册失败!');
            }
        }else{
            return $this->Error('请输入完整用户信息!');
        }
    }
    //信息
    public function info()
    {
        return json_encode(Auth::user());
    }

    public function update(Request $request)
    {
        if($request->has('username') && $request->has('oldpassword') && $request->has('newpassword')){
            $username = $request->input('username');
            $oldpassword = sha1($this->salt.$request->input('oldpassword'));
            $newpassword = sha1($this->salt.$request->input('newpassword'));

            $userExist = User::getUserByName($username);
            if ($userExist) {
                $userModel = User::getUserByNamePassword($username, $oldpassword);
                if ($userModel) {
                    $updateResult = User::userUpdate($userModel, $newpassword);
                    if ($updateResult) {
                        return $this->Success('更新成功!');
                    } else {
                        return $this->Error('更新失败!');
                    }
                } else {
                    return $this->Error('密码错误!');
                }
            } else {
                return $this->Error('用户名错误!');
            }
        }else{
            return $this->Error('请输入完整用户信息!');
        }
    }

    public function getProduct(Request $request)
    {
        if($request->has('username') ){
            $username = $request->input('username');
            $productList = User::getProductByUserName($username);

            return $productList;
        }else{
            return $this->Error('请输入用户名');
        }
    }

    public function userProduct(Request $request)
    {
        if($request->has('user_id') && $request->has('product_ids')){
            $userId = $request->input('user_id');
            $productIds = $request->input('product_ids');
            $updateStatus = User::userProduct($userId, $productIds);
            if ($updateStatus) {
                return $this->Success("更新成功");
            } else {
                return $this->Error("更新失败");
            }
        }

    }

}
