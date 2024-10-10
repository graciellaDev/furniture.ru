<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Http\Resources\ProductCollection;

class ProductController extends Controller
{
    public static function update(string $id) {
        $data = [
            'name',
            'description',
            'price',
            'status'
        ];
        foreach ($data as $key => $value) {
            if (!empty(request()->post($value))) {
                switch ($value) {
                    case 'price' :
                        $data[$value] = (int)request()->post($value);
                        break;
                    case 'status' :
                        $data[$value] = (int)request()->post($value) == 1 ? 1 : 0;
                        break;
                    default :
                        $data[$value] = request()->post($value);
                }
            }
            unset($data[$key]);
        }

        $product = Product::find((int)$id);
        if (!empty($product)) {
            $statusUpdate = $product->update($data);
        } else {
            $statusUpdate = false;
        }

        return response()->json(
            data: $statusUpdate ? 'Товар успешно обновлен' : 'Ошибка обновления',
            status: $statusUpdate ? 200 : 404
        );
    }

    public static function delete(string $id) {
        $product = Product::find((int)$id);

        if (!empty($product)) {
            $statusUpdate = $product->delete();
        } else {
            $statusUpdate = false;
        }

        return response()->json(
            data: $statusUpdate ? 'Товар успешно удален' : 'Ошибка удаления',
            status: $statusUpdate ? 200 : 404
        );
    }

    public function add() {
        $name = request()->post('name');
        $description = request()->post('description');
        $price = request()->post('price');
        $status = request()->post('status');
        $count = request()->post('count') ? request()->post('count') : 1;
        $count = min($count, 1000);

        $statusCreate = Product::factory()->count($count)
            ->create([
                'name' => $name ?: 'Default name' ,
                'description' => $description ?: 'Default description',
                'price' => !empty($price) ? $price : null,
                'status' => $status == 1 ? 1 : 0
            ]);

        return response()->json(
            data: $statusCreate ? 'Товар успешно создан' : 'Ошибка создания товара',
            status: $statusCreate ? 200 : 500
        );
    }

    public function list() {
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
    }
}
