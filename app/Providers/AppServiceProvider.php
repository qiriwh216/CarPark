<?php

namespace App\Providers;

use App\Helpers\ICEService;
use App\Helpers\MyLog;
use App\Helpers\NewMicroAuthPrivilege;
use App\Http\Services\UserService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {

        $this->app->singleton(
            'ICEService',
            function () {
                return  ICEService::getInstance();
            }
        );

        $this->app->singleton(
            'userService',
            function () {
                return  new UserService(ICEService::getInstance(), NewMicroAuthPrivilege::getInstance());
            }
        );

        $this->app->singleton(
            'myLog',
            function () {
                return  new MyLog();
            }
        );

        \API::error(function (\Illuminate\Auth\Access\AuthorizationException $exception) {
            abort(403, '没有此权限');
        });
        \API::error(function ( \Illuminate\Database\Eloquent\ModelNotFoundException $exception) {
            abort(404, '该模型未找到');
        });
        \API::error(function ( \Symfony\Component\HttpKernel\Exception\NotFoundHttpException $exception) {
            abort(404, '找不到该页面');
        });
       


    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
