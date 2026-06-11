<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Codedge\Fpdf\Fpdf\Fpdf;
use App\Models\Quotation;
use App\Models\Package;

class QuotationController extends Controller
{
    public function index(Request $request)
    {
        $quotations = Quotation::active()->when($request->search, function ($query, $search) {
            return $query->where('name', 'like', '%' . $search . '%');
        })->orderBy('id', 'desc')->paginate(20);
        $packages = Package::active()->get();
        return view('quotations.index', compact('quotations', 'packages'));
    }

    public function store(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'phone' => 'required',
            'package_id' => 'required',
            'people_number' => 'required|integer',
            'date' => 'required|date',
            'event_date' => 'required|date',
            'visit_date' => 'nullable|date',
            'answer_date' => 'required|date'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'error' => $validator->errors()->first()
            ]);
        }

        Quotation::create($request->all());

        return response()->json([
            'status' => true
        ]);
    }

    public function edit(Request $request, Quotation $quotation)
    {
        return response()->json([
            'id' => $quotation->id,
            'name' => $quotation->name,
            'phone' => $quotation->phone,
            'package_id' => $quotation->package_id,
            'people_number' => $quotation->people_number,
            'date' => $quotation->date->format('Y-m-d'),
            'event_date' => $quotation->event_date->format('Y-m-d'),
            'visit_date' => $quotation->visit_date ? $quotation->visit_date->format('Y-m-d') : null,
            'answer_date' => $quotation->answer_date->format('Y-m-d'),
            'observations' => $quotation->observations,
        ]);
    }

    public function update(Request $request, Quotation $quotation)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'phone' => 'required',
            'package_id' => 'required',
            'people_number' => 'required|integer',
            'date' => 'required|date',
            'event_date' => 'required|date',
            'visit_date' => 'nullable|date',
            'answer_date' => 'required|date'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'error' => $validator->errors()->first()
            ]);
        }

        $quotation->update($request->all());

        return response()->json([
            'status' => true
        ]);
    }

    public function destroy(Request $request, Quotation $quotation)
    {
        $quotation->update([
            'deleted' => 1
        ]);

        return response()->json([
            'status' => true
        ]);
    }

    public function pdf(Request $request, Quotation $quotation)
    {
        $fpdf = new Fpdf();

        $fpdf->AddPage();

        $fpdf->AddFont('Montserrat', '');
        $fpdf->AddFont('Montserrat', 'B');

        $fpdf->Image(public_path('assets/images/logonew2.png'), 15, 15, 45);

        $fpdf->Ln(20);

        $fpdf->SetFont('Montserrat', 'B', 14);

        $fpdf->Cell(190, 5, utf8_decode('COTIZACIÓN DE EVENTO'), 0, 1, 'C');

        $fpdf->Ln();

        $fpdf->SetFont('Montserrat', '', 12);

        $fpdf->Cell(190, 5, 'Chiclayo, ' . $quotation->date->format('d/m/Y'), 0, 1);

        $fpdf->Ln();


        $fpdf->SetFont('Montserrat', 'B', 12);

        $fpdf->Cell(50, 10, 'Nombre:');

        $fpdf->SetFont('Montserrat', '', 12);

        $fpdf->Cell(140, 10, utf8_decode($quotation->name), 0, 1);

        $fpdf->SetFont('Montserrat', 'B', 12);

        $fpdf->Cell(50, 10, utf8_decode('Teléfono:'));

        $fpdf->SetFont('Montserrat', '', 12);

        $fpdf->Cell(140, 10, utf8_decode($quotation->phone), 0, 1);

        $fpdf->SetFont('Montserrat', 'B', 12);

        $fpdf->Cell(50, 10, 'Paquete:');

        $fpdf->SetFont('Montserrat', '', 12);

        $fpdf->Cell(140, 10, utf8_decode(optional($quotation->package)->name), 0, 1);

        $fpdf->SetFont('Montserrat', 'B', 12);

        $fpdf->Cell(50, 10, utf8_decode('Número de personas:'));

        $fpdf->SetFont('Montserrat', '', 12);

        $fpdf->Cell(140, 10, $quotation->people_number, 0, 1);

        $fpdf->SetFont('Montserrat', 'B', 12);

        $fpdf->Cell(50, 10, utf8_decode('Fecha de evento:'));

        $fpdf->SetFont('Montserrat', '', 12);

        $fpdf->Cell(140, 10, $quotation->event_date->format('d/m/Y'), 0, 1);

        if ($quotation->visit_date) {
            $fpdf->SetFont('Montserrat', 'B', 12);
            $fpdf->Cell(50, 10, utf8_decode('Fecha de visita:'));
            $fpdf->SetFont('Montserrat', '', 12);
            $fpdf->Cell(140, 10, $quotation->visit_date->format('d/m/Y'), 0, 1);
        }

        $fpdf->SetFont('Montserrat', 'B', 12);

        $fpdf->Cell(50, 10, utf8_decode('Fecha de respuesta:'));

        $fpdf->SetFont('Montserrat', '', 12);

        $fpdf->Cell(140, 10, $quotation->answer_date->format('d/m/Y'), 0, 1);

        if ($quotation->observations) {
            $fpdf->SetFont('Montserrat', 'B', 12);
            $fpdf->Cell(50, 10, utf8_decode('Observaciones:'));
            $fpdf->SetFont('Montserrat', '', 12);
            $fpdf->MultiCell(140, 10, utf8_decode($quotation->observations), 0, 1);
        }




        $filename = 'Cotizacion_' . $quotation->id . '.pdf';

        $fpdf->Output('I', $filename);
    }
}
