<?php
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\Product;
use App\Http\Resources\ProductCollection;
use App\Http\Controllers\api\AuthController;
use App\Http\Controllers\ProductController;

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

    Route::get('/products', function () {
        $products = Product::select(['*']);
        $limit = request()->limit;
        $filters = request()->get('filters');

        if (!empty($filters)) {
            $filters = explode(';', $filters);
            foreach ($filters as $key => $value) {
                $value = explode(':', $value);
                if (!(count($value) == 1 || $value[1] == '')) {
                    $filters[$value[0]] = explode(',', $value[1]);
                }
                unset($filters[$key]);
            }
            foreach ($filters as $key => $value) {
                switch ($key) {
                    case 'price':
                        $products = $products->where('price', $value);
                        break;
                    case 'status':
                        $products = $products->where('status', $value);
                        break;
                    default:
                        break;
                }
            }
        }

        return new ProductCollection($products->paginate($limit));
    });


    Route::get('/product/{id}', function (string $id) {

        return new ProductCollection(Product::findOrFail($id));
    });

});
