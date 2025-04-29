<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $query = Product::query();
        if($search = $request->query('search'))
        {
            $query->where('name', 'like', "%$search%")->orWhereJsonContains('tags', $search);
        }
        $perPage = $request->query('per_page', 10);
        $products = $query->paginate($perPage);
        return response()->json($products);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'price' => 'required|numeric|min:0',
            'tags' => 'array',
        ]);
        $tags = array_filter($validated['tags'], function ($tag)
        {
            return !in_array($tag, [null, false, 0, '0', 'undefined'], true);
        });
        $product = Product::create([
            'name' => $validated['name'],
            'price' => $validated['price'],
            'tags' => json_encode($tags),
        ]);
        return response()->json(['message' => 'Producto creado', 'product' => $product], 201);
    }

    public function show($id)
    {
        $product = Product::find($id);
        if(!$product)
        {
            return response()->json(['message' => 'Producto no encontrado'], 404);
        }
        return response()->json($product);
    }

    public function update(Request $request, $id)
    {
        $product = Product::find($id);
        if(!$product)
        {
            return response()->json(['message' => 'Producto no encontrado'], 404);
        }
        $request->validate([
            'name' => 'string',
            'price' => 'numeric',
            'tags' => 'array',
        ]);
        $product->update($request->only('name', 'price', 'tags'));
        return response()->json(['message' => 'Producto actualizado', 'product' => $product], 201);
    }

    public function delete($id)
    {
        $product = Product::find($id);
        if(!$product)
        {
            return response()->json(['message' => 'Producto no encontrado'], 404);
        }
        $product->delete();
        return response()->json(['message' => 'Producto eliminado']);
    }

    // Esta funcion la deberia agregar a un helper, pero al no utilizarla en otra parte la deje en el controller
    public function multiply(int $a, int $b): int
    {
        $result = 0;
        $positive = $b >= 0;
        for($i = 0; $i < abs($b); $i++)
        {
            $result += $a;
        }
        return $positive ? $result : -$result;
    }

    public function totalStockValue($id, Request $request)
    {
        $quantity = (int) $request->query('quantity', 1);
        $product = Product::find($id);
        if(!$product)
        {
            return response()->json(['message' => 'Producto no encontrado'], 404);
        }
        $total = $this->multiply($product->price, $quantity);
        return response()->json([
            'productId' => $product->id,
            'price' => $product->price,
            'quantity' => $quantity,
            'totalValue' => $total
        ]);
    }

    public function highestPrice()
    {
        $products = Product::all();
        if($products->isEmpty())
        {
            return response()->json(['message' => 'No hay productos'], 404);
        }
        $prices = $products->pluck('price')->toArray();
        $highestPrice = -1; 
        foreach($prices as $price)
        {
            if($price > $highestPrice)
            {
                $highestPrice = $price;
            }
        }
        $highestPriceProducts = $products->where('price', $highestPrice)->values()->all();
        return response()->json($highestPriceProducts, 200);
    }

    public function mostUsedTag()
    {
        $products = Product::all();
        if($products->isEmpty())
        {
            return response()->json(['message' => 'No hay productos'], 404);
        }
        $tags = [];
        foreach($products as $product)
        {
            if(isset($product->tags))
            {
                $productTags = is_string($product->tags) ? json_decode($product->tags, true) : $product->tags;
                if(is_array($productTags))
                {
                    foreach($productTags as $tag)
                    {
                        if(isset($tags[$tag]))
                        {
                            $tags[$tag]['count']++;
                            $tags[$tag]['products'][] = $product;
                        }
                        else
                        {
                            $tags[$tag] = [
                                'count' => 1,
                                'products' => [$product],
                            ];
                        }
                    }
                }
            }
        }
        $mostUsedTag = null;
        $maxCount = 0;
        $productsWithTag = [];
        foreach($tags as $tag => $data)
        {
            if($data['count'] > $maxCount)
            {
                $mostUsedTag = $tag;
                $maxCount = $data['count'];
                $productsWithTag = $data['products'];
            }
        }
        if($mostUsedTag !== null)
        {
            return response()->json([
                'tag' => $mostUsedTag,
                'count' => $maxCount,
                'products' => $productsWithTag
            ]);
        }
        return response()->json(['message' => 'No se encontraron etiquetas'], 404);
    }
}
