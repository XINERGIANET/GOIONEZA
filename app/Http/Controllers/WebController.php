<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Codedge\Fpdf\Fpdf\Fpdf;
use App\Models\Contract;
use App\Models\Income;
use App\Models\Expense;
use App\Models\Purchase;
use App\Models\Payment;

class WebController extends Controller
{
    public function index(Request $request)
    {
        if (auth()->user()->role == 'almacen') {
            return redirect()->route('products.index');
        }

        $contracts = Contract::active()->when($request->start_date, function ($query, $start_date) {
            return $query->whereDate('date', '>=', $start_date);
        })->when($request->end_date, function ($query, $end_date) {
            return $query->whereDate('date', '<=', $end_date);
        });

        $payments = Payment::when($request->start_date, function ($query, $start_date) {
            return $query->whereDate('date', '>=', $start_date);
        })->when($request->end_date, function ($query, $end_date) {
            return $query->whereDate('date', '<=', $end_date);
        });

        $incomes = Income::active()->when($request->start_date, function ($query, $start_date) {
            return $query->whereDate('date', '>=', $start_date);
        })->when($request->end_date, function ($query, $end_date) {
            return $query->whereDate('date', '<=', $end_date);
        });

        $expenses = Expense::active()->when($request->start_date, function ($query, $start_date) {
            return $query->whereDate('date', '>=', $start_date);
        })->when($request->end_date, function ($query, $end_date) {
            return $query->whereDate('date', '<=', $end_date);
        });

        $purchases = Purchase::active()->when($request->start_date, function ($query, $start_date) {
            return $query->whereDate('date', '>=', $start_date);
        })->when($request->end_date, function ($query, $end_date) {
            return $query->whereDate('date', '<=', $end_date);
        });

        $total_contracts = (clone $contracts)->sum('initial_payment');
        $total_payments = (clone $payments)->sum('amount');
        $total_incomes = (clone $incomes)->sum('amount');
        $total_expenses = (clone $expenses)->sum('amount');
        $total_purchases = (clone $purchases)->sum('amount');

        /********** Cierre de caja **********/

        $contracts_efectivo = (clone $contracts)->where('payment_method_id', 1)->sum('initial_payment');
        $contracts_bcp = (clone $contracts)->where('payment_method_id', 2)->sum('initial_payment');
        $contracts_bbva = (clone $contracts)->where('payment_method_id', 3)->sum('initial_payment');
        $contracts_sck = (clone $contracts)->where('payment_method_id', 4)->sum('initial_payment');
        $contracts_ibk = (clone $contracts)->where('payment_method_id', 5)->sum('initial_payment');

        $payments_efectivo = (clone $payments)->where('payment_method_id', 1)->sum('amount');
        $payments_bcp = (clone $payments)->where('payment_method_id', 2)->sum('amount');
        $payments_bbva = (clone $payments)->where('payment_method_id', 3)->sum('amount');
        $payments_sck = (clone $payments)->where('payment_method_id', 4)->sum('amount');
        $payments_ibk = (clone $payments)->where('payment_method_id', 5)->sum('amount');

        $incomes_efectivo = (clone $incomes)->where('payment_method_id', 1)->sum('amount');
        $incomes_bcp = (clone $incomes)->where('payment_method_id', 2)->sum('amount');
        $incomes_bbva = (clone $incomes)->where('payment_method_id', 3)->sum('amount');
        $incomes_sck = (clone $incomes)->where('payment_method_id', 4)->sum('amount');
        $incomes_ibk = (clone $incomes)->where('payment_method_id', 5)->sum('amount');

        $expenses_efectivo = (clone $expenses)->where('payment_method_id', 1)->sum('amount');
        $expenses_bcp = (clone $expenses)->where('payment_method_id', 2)->sum('amount');
        $expenses_bbva = (clone $expenses)->where('payment_method_id', 3)->sum('amount');
        $expenses_sck = (clone $expenses)->where('payment_method_id', 4)->sum('amount');
        $expenses_ibk = (clone $expenses)->where('payment_method_id', 5)->sum('amount');

        $purchases_efectivo = (clone $purchases)->where('payment_method_id', 1)->sum('amount');
        $purchases_bcp = (clone $purchases)->where('payment_method_id', 2)->sum('amount');
        $purchases_bbva = (clone $purchases)->where('payment_method_id', 3)->sum('amount');
        $purchases_sck = (clone $purchases)->where('payment_method_id', 4)->sum('amount');
        $purchases_ibk = (clone $purchases)->where('payment_method_id', 5)->sum('amount');

        $efectivo = $contracts_efectivo + $payments_efectivo + $incomes_efectivo - $expenses_efectivo - $purchases_efectivo;
        $bcp = $contracts_bcp + $payments_bcp + $incomes_bcp - $expenses_bcp - $purchases_bcp;
        $bbva = $contracts_bbva + $payments_bbva + $incomes_bbva - $expenses_bbva - $purchases_bbva;
        $sck = $contracts_sck + $payments_sck + $incomes_sck - $expenses_sck - $purchases_sck;
        $ibk = $contracts_ibk + $payments_ibk + $incomes_ibk - $expenses_ibk - $purchases_ibk;

        /********** Cuentas por cobrar **********/

        $total_charges = Contract::active()->when($request->start_date, function ($query, $start_date) {
            return $query->whereDate('debt_payment_date', '>=', $start_date);
        })->when($request->end_date, function ($query, $end_date) {
            return $query->whereDate('debt_payment_date', '<=', $end_date);
        })->sum('debt');

        /********** Cuentas por cobrar **********/

        $events = Contract::active()->whereDate('event_date', '>', now())->orderBy('event_date', 'asc')->limit(3)->get();

        return view('index', compact('total_contracts', 'total_payments', 'total_incomes', 'total_expenses', 'total_purchases', 'efectivo', 'bcp', 'bbva', 'sck', 'ibk', 'total_charges', 'events'));
    }

    public function calendar()
    {
        return view('calendar');
    }

    public function events(Request $request)
    {
        $contracts = Contract::with('package', 'event_type')->active()->whereDate('event_date', '>=', $request->start)->whereDate('event_date', '<=', $request->end)->get();
        $events = $contracts->map(function ($contract) {
            return [
            'id' => 'contract_'.$contract->id,
            'title' => 'Contrato',
            'start' => $contract->event_date->format('Y-m-d'),
            'end' => $contract->event_date->format('Y-m-d'),
            'name' => $contract->name,
            'package' => optional($contract->package)->name,
            'people_number' => $contract->people_number,
            'event_date' => $contract->event_date->format('d/m/Y'),
            'event_time' => $contract->event_time ? $contract->event_time->format('H:i') : '',
            'event_type' => optional($contract->event_type)->name,
            'location' => optional($contract->location)->name,
            'color' => '#28a745',
            'is_quotation' => false
            ];
        });

        $quotations = \App\Models\Quotation::with('package')->active()->whereNotNull('visit_date')->whereDate('visit_date', '>=', $request->start)->whereDate('visit_date', '<=', $request->end)->get();
        $visit_events = $quotations->map(function ($quotation) {
            return [
            'id' => 'quotation_'.$quotation->id,
            'title' => 'Visita',
            'start' => $quotation->visit_date->format('Y-m-d'),
            'end' => $quotation->visit_date->format('Y-m-d'),
            'name' => $quotation->name,
            'package' => optional($quotation->package)->name,
            'people_number' => $quotation->people_number,
            'event_date' => $quotation->event_date ? $quotation->event_date->format('d/m/Y') : '',
            'event_time' => '',
            'event_type' => '',
            'location' => '',
            'visit_date' => $quotation->visit_date->format('d/m/Y'),
            'phone' => $quotation->phone,
            'color' => '#ffc107',
            'textColor' => '#000000',
            'is_quotation' => true
            ];
        });

        $all_events = $events->concat($visit_events);

        return response()->json($all_events);
    }

    public function cash_flow()
    {
        $year = request()->year ? request()->year : now()->format('Y');
        $totals = [
            'incomes' => [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
            'expenses' => [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0]
        ];
        return view('cash_flow', compact('year', 'totals'));
    }

    public function cash_report(Request $request)
    {

        $fpdf = new Fpdf();

        $fpdf->AddPage();

        $fpdf->AddFont('Montserrat', '');
        $fpdf->AddFont('Montserrat', 'B');

        $fpdf->SetFont('Montserrat', 'B', 16);

        $fpdf->Image(public_path('assets/images/logonew2.png'), 15, 15, 45);

        $fpdf->Ln(20);

        $fpdf->Cell(190, 10, 'CIERRE DE CAJA', 0, 1, 'C');

        $fpdf->SetFont('Montserrat', '', 14);

        $fpdf->Cell(190, 10, 'Fecha inicial: ' . $request->start_date, 0, 1);

        $fpdf->Cell(190, 10, 'Fecha final: ' . $request->end_date, 0, 1);

        $fpdf->Ln();

        $contracts = Contract::active()->when($request->start_date, function ($query, $start_date) {
            return $query->whereDate('date', '>=', $start_date);
        })->when($request->end_date, function ($query, $end_date) {
            return $query->whereDate('date', '<=', $end_date);
        });

        $payments = Payment::when($request->start_date, function ($query, $start_date) {
            return $query->whereDate('date', '>=', $start_date);
        })->when($request->end_date, function ($query, $end_date) {
            return $query->whereDate('date', '<=', $end_date);
        });

        $incomes = Income::active()->when($request->start_date, function ($query, $start_date) {
            return $query->whereDate('date', '>=', $start_date);
        })->when($request->end_date, function ($query, $end_date) {
            return $query->whereDate('date', '<=', $end_date);
        });

        $expenses = Expense::active()->when($request->start_date, function ($query, $start_date) {
            return $query->whereDate('date', '>=', $start_date);
        })->when($request->end_date, function ($query, $end_date) {
            return $query->whereDate('date', '<=', $end_date);
        });

        $purchases = Purchase::active()->when($request->start_date, function ($query, $start_date) {
            return $query->whereDate('date', '>=', $start_date);
        })->when($request->end_date, function ($query, $end_date) {
            return $query->whereDate('date', '<=', $end_date);
        });

        $total_contracts = (clone $contracts)->sum('initial_payment');
        $total_payments = (clone $payments)->sum('amount');
        $total_incomes = (clone $incomes)->sum('amount');
        $total_expenses = (clone $expenses)->sum('amount');
        $total_purchases = (clone $purchases)->sum('amount');

        /********** Cierre de caja **********/

        $contracts_efectivo = (clone $contracts)->where('payment_method_id', 1)->sum('initial_payment');
        $contracts_bcp = (clone $contracts)->where('payment_method_id', 2)->sum('initial_payment');
        $contracts_bbva = (clone $contracts)->where('payment_method_id', 3)->sum('initial_payment');
        $contracts_sck = (clone $contracts)->where('payment_method_id', 4)->sum('initial_payment');
        $contracts_ibk = (clone $contracts)->where('payment_method_id', 5)->sum('initial_payment');

        $payments_efectivo = (clone $payments)->where('payment_method_id', 1)->sum('amount');
        $payments_bcp = (clone $payments)->where('payment_method_id', 2)->sum('amount');
        $payments_bbva = (clone $payments)->where('payment_method_id', 3)->sum('amount');
        $payments_sck = (clone $payments)->where('payment_method_id', 4)->sum('amount');
        $payments_ibk = (clone $payments)->where('payment_method_id', 5)->sum('amount');

        $incomes_efectivo = (clone $incomes)->where('payment_method_id', 1)->sum('amount');
        $incomes_bcp = (clone $incomes)->where('payment_method_id', 2)->sum('amount');
        $incomes_bbva = (clone $incomes)->where('payment_method_id', 3)->sum('amount');
        $incomes_sck = (clone $incomes)->where('payment_method_id', 4)->sum('amount');
        $incomes_ibk = (clone $incomes)->where('payment_method_id', 5)->sum('amount');

        $expenses_efectivo = (clone $expenses)->where('payment_method_id', 1)->sum('amount');
        $expenses_bcp = (clone $expenses)->where('payment_method_id', 2)->sum('amount');
        $expenses_bbva = (clone $expenses)->where('payment_method_id', 3)->sum('amount');
        $expenses_sck = (clone $expenses)->where('payment_method_id', 4)->sum('amount');
        $expenses_ibk = (clone $expenses)->where('payment_method_id', 5)->sum('amount');

        $purchases_efectivo = (clone $purchases)->where('payment_method_id', 1)->sum('amount');
        $purchases_bcp = (clone $purchases)->where('payment_method_id', 2)->sum('amount');
        $purchases_bbva = (clone $purchases)->where('payment_method_id', 3)->sum('amount');
        $purchases_sck = (clone $purchases)->where('payment_method_id', 4)->sum('amount');
        $purchases_ibk = (clone $purchases)->where('payment_method_id', 5)->sum('amount');

        $efectivo = $contracts_efectivo + $payments_efectivo + $incomes_efectivo - $expenses_efectivo - $purchases_efectivo;
        $bcp = $contracts_bcp + $payments_bcp + $incomes_bcp - $expenses_bcp - $purchases_bcp;
        $bbva = $contracts_bbva + $payments_bbva + $incomes_bbva - $expenses_bbva - $purchases_bbva;
        $sck = $contracts_sck + $payments_sck + $incomes_sck - $expenses_sck - $purchases_sck;
        $ibk = $contracts_ibk + $payments_ibk + $incomes_ibk - $expenses_ibk - $purchases_ibk;

        $fpdf->SetFont('Montserrat', '', 14);

        $fpdf->Cell(190, 10, 'Efectivo: S/' . number_format($efectivo, 2), 0, 1);
        $fpdf->Cell(190, 10, 'BCP: S/' . number_format($bcp, 2), 0, 1);
        $fpdf->Cell(190, 10, 'BBVA: S/' . number_format($bbva, 2), 0, 1);
        $fpdf->Cell(190, 10, 'SCK: S/' . number_format($sck, 2), 0, 1);
        $fpdf->Cell(190, 10, 'IBK: S/' . number_format($ibk, 2), 0, 1);

        $filename = 'CierreCaja.pdf';

        $fpdf->Output('D', $filename);

    }

    public function help()
    {
        return view('help');
    }

    public function searchDni(Request $request)
    {
        $dni = $request->numero;
        $token = env('APIRENIEC_TOKEN');
        
        // Remove quotes if they exist in the env variable just in case
        $token = trim($token, '"\'');
        
        $url = env('APIRENIEC_URL') . '?numero=' . $dni;

        $response = \Illuminate\Support\Facades\Http::withToken($token)->get($url);

        if ($response->successful()) {
            return response()->json(array_merge(['status' => true], $response->json()));
        }

        return response()->json([
            'status' => false,
            'error' => 'DNI no encontrado o error en la API: ' . $response->body()
        ]);
    }
}
