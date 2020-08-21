<?php

namespace App\Packages\FamilyPackage;

use Illuminate\Support\Facades\App;
use Illuminate\Support\ServiceProvider;

class FamilyServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        App::bind('Family',function (){

            echo "this sis family binding <br/>";
            return new Family();
        });

    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
