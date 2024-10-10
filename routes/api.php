<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\Product;
use App\Http\Resources\ProductCollection;
use App\Http\Controllers\api\AuthController;
use App\Http\Controllers\api\ProductController;

Route::controller(AuthController::class)->group(function () {
    Route::post('login', 'login');
    Route::post('register', 'register');
    Route::get('profile', 'profile');
});

Route::group(['middleware' => 'auth:api'], function () {
    Route::controller(AuthController::class)->group(function () {
        Route::get('profile',  'profile');
        Route::get('refresh', 'refresh');
        Route::get('logout', 'logout');
        Route::post('register', 'register');
    });

    Route::get('/products', [ProductController::class, 'list']);
    Route::post('add/product', [ProductController::class, 'add']);
    Route::match(['get', 'put', 'delete'], '/product/{id}', function (string $id) {
        $method = request()->method();
        switch ($method) {
            case 'GET' :
                return new ProductCollection(Product::findOrFail($id));
            case 'PUT':
                return ProductController::update($id);
            case 'DELETE' :
                return ProductController::delete($id);
        }
    });
});
