<?php

return [
    App\Providers\AppServiceProvider::class,
    App\Providers\HorizonServiceProvider::class,
    // TelescopeServiceProvider only loads in local environment via composer.json extra.laravel.dont-discover
];
