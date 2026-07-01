<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Codedge\Fpdf\Fpdf\Fpdf;
use App\Models\Product;
use App\Models\ProductType;
use App\Models\Movement;
use App\Models\Location;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $products = Product::active()->when($request->product_type_id, function ($query, $product_type_id) {
            return $query->where('product_type_id', $product_type_id);
        })->when($request->location_id, function ($query, $location_id) {
            return $query->where(function($q) use ($location_id) {
                $q->where('location_id', $location_id);
                // Also search by text for legacy data compatibility
                $location = Location::find($location_id);
                if($location) {
                    $q->orWhere('location', $location->name);
                }
            });
        })->when($request->sublocation_id, function ($query, $sublocation_id) {
            return $query->where('sublocation_id', $sublocation_id);
        })->when($request->search, function ($query, $search) {
            return $query->where('name', 'like', '%' . $search . '%');
        })->orderBy('name', 'asc')->paginate(20);

        $product_types = ProductType::active()->get();
        $locations = Location::active()->get();

        return view('products.index', compact('products', 'product_types', 'locations'));
    }

    public function store(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'product_type_id' => 'required',
            'location_id' => 'required',
            'stock' => 'required|integer'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'error' => $validator->errors()->first()
            ]);
        }
        
        if($request->has('location_id') && $request->location_id != '') {
            $location_model = Location::find($request->location_id);
            if($location_model) {
                $request->merge(['location' => $location_model->name]);
            }
        }

        $type = ProductType::find($request->product_type_id);

        $code = substr($type->name, 0, 3) . '-' . substr(str_replace(' ', '', $request->name), 0, 3);
        $code = mb_strtoupper($code, 'UTF-8');


        $product = Product::where('code', 'like', $code . '%')->get();

        $number = $product->count() + 1;

        $number = str_pad($number, 3, "0", STR_PAD_LEFT);

        $code = $code . '-' . $number;

        $request->merge([
            'code' => $code
        ]);

        Product::create($request->all());

        return response()->json([
            'status' => true
        ]);
    }

    public function edit(Request $request, Product $product)
    {
        return response()->json($product);
    }

    public function update(Request $request, Product $product)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'product_type_id' => 'required',
            'location_id' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'error' => $validator->errors()->first()
            ]);
        }
        
        if($request->has('location_id') && $request->location_id != '') {
            $location_model = Location::find($request->location_id);
            if($location_model) {
                $request->merge(['location' => $location_model->name]);
            }
        }

        $product->update($request->all());

        return response()->json([
            'status' => true
        ]);
    }

    public function destroy(Request $request, Product $product)
    {
        $product->update([
            'deleted' => 1
        ]);

        return response()->json([
            'status' => true
        ]);
    }

    public function pdf()
    {
        $fpdf = new Fpdf();

        $fpdf->AddPage();

        $fpdf->AddFont('Montserrat', '');
        $fpdf->AddFont('Montserrat', 'B');

        $fpdf->Image(public_path('assets/images/logonew2.png'), 15, 15, 45);

        $fpdf->Ln(20);

        $fpdf->SetFont('Montserrat', 'B', 14);

        $fpdf->Cell(190, 5, 'ALMACEN', 0, 1, 'C');

        $fpdf->Ln();

        $product_types = ProductType::active()->get();

        foreach ($product_types as $product_type) {

            $fpdf->SetFont('Montserrat', 'B', 14);

            $fpdf->Cell(190, 5, $product_type->name, 0, 1);

            $fpdf->Ln();

            $products = Product::active()->where('product_type_id', $product_type->id)->orderBy('name', 'asc')->get();

            $fpdf->SetFont('Montserrat', 'B', 12);

            $fpdf->Cell(130, 10, utf8_decode('Nombre'), 1);
            $fpdf->Cell(30, 10, utf8_decode('Ubicación'), 1);
            $fpdf->Cell(30, 10, utf8_decode('Stock'), 1);
            $fpdf->Ln();

            $fpdf->SetFont('Montserrat', '', 12);

            foreach ($products as $product) {
                $fpdf->Cell(130, 8, utf8_decode($product->name), 1);
                $fpdf->Cell(30, 8, utf8_decode($product->location), 1);
                $fpdf->Cell(30, 8, $product->stock, 1);
                $fpdf->Ln();
            }

            $fpdf->Ln();

        }

        $fpdf->Output();
    }

    public function decrement(Request $request, Product $product)
    {
        $validator = Validator::make($request->all(), [
            'quantity' => 'required|integer'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'error' => $validator->errors()->first()
            ]);
        }

        $quantity = intval($request->quantity);
        $stock = intval($product->stock) - $quantity;

        Movement::create([
            'product_id' => $product->id,
            'type' => 'Salida',
            'quantity' => $quantity,
            'stock' => $stock,
            'date' => now()
        ]);

        $product->update([
            'stock' => $stock
        ]);

        return response()->json([
            'status' => true
        ]);
    }

    public function increment(Request $request, Product $product)
    {
        $validator = Validator::make($request->all(), [
            'quantity' => 'required|integer'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'error' => $validator->errors()->first()
            ]);
        }

        $quantity = intval($request->quantity);
        $stock = intval($product->stock) + $quantity;

        Movement::create([
            'product_id' => $product->id,
            'type' => 'Entrada',
            'quantity' => $quantity,
            'stock' => $stock,
            'date' => now()
        ]);

        $product->update([
            'stock' => $stock
        ]);

        return response()->json([
            'status' => true
        ]);
    }

    public function movements(Request $request, Product $product)
    {
        $movements = Movement::where('product_id', $product->id)->orderBy('date', 'desc')->get();

        return response()->json([
            'name' => $product->name,
            'movements' => $movements->map(function ($movement) {
            return [
                    'type' => $movement->type,
                    'quantity' => $movement->quantity,
                    'stock' => $movement->stock,
                    'date' => $movement->date->format('d/m/Y H:i'),
                ];
        })
        ]);
    }

}
