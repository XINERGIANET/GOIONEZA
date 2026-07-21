<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use App\Models\Expense;
use App\Models\Contract;
use App\Models\PaymentMethod;
use Codedge\Fpdf\Fpdf\Fpdf;

class ExpenseController extends Controller
{
    public function index(Request $request){
        $expenses = Expense::active()->when($request->description, function($query, $description){
            return $query->where('description', 'like', '%'.$description.'%');
        })->when($request->start_date, function($query, $start_date){
            return $query->whereDate('date', '>=', $start_date);
        })->when($request->end_date, function($query, $end_date){
            return $query->whereDate('date', '<=', $end_date);
        })->latest('date');

        $total = $expenses->sum('amount');
        $expenses = $expenses->paginate(20);

        $contracts = Contract::active()->get();
        $payment_methods = PaymentMethod::where('name', 'not like', '%(Inactivo)%')->get();
        return view('expenses.index', compact('expenses', 'contracts', 'payment_methods', 'total'));
    }

    public function store(Request $request){

        $validator = Validator::make($request->all(), [
            'contract_id' => 'required',
            'description' => 'required',
            'responsible' => 'required',
            'voucher' => 'required',
            'voucher_number' => 'required',
            'provider' => 'required',
            'amount' => 'required|numeric',
            'payment_method_id' => 'required',
            'date' => 'required|date'
        ]);

        if($validator->fails()){
            return response()->json([
                'status' => false,
                'error' => $validator->errors()->first()
            ]);
        }

        Expense::create($request->all());

        return response()->json([
            'status' => true
        ]);
    }

    public function edit(Request $request, Expense $expense){
        return response()->json([
            'id' => $expense->id,
            'contract_id' => $expense->contract_id,
            'description' => $expense->description,
            'responsible' => $expense->responsible,
            'voucher' => $expense->voucher,
            'voucher_number' => $expense->voucher_number,
            'provider' => $expense->provider,
            'amount' => $expense->amount,
            'payment_method_id' => $expense->payment_method_id,
            'date' => $expense->date->format('Y-m-d'),
        ]);
    }

    public function update(Request $request, Expense $expense){
        $validator = Validator::make($request->all(), [
            'contract_id' => 'required',
            'description' => 'required',
            'responsible' => 'required',
            'voucher' => 'required',
            'voucher_number' => 'required',
            'provider' => 'required',
            'amount' => 'required|numeric',
            'payment_method_id' => 'required',
            'date' => 'required|date'
        ]);

        if($validator->fails()){
            return response()->json([
                'status' => false,
                'error' => $validator->errors()->first()
            ]);
        }

        $expense->update($request->all());

        return response()->json([
            'status' => true
        ]);
    }

    public function destroy(Request $request, Expense $expense){
        $expense->update([
            'deleted' => 1
        ]);

        return response()->json([
            'status' => true
        ]);
    }

    public function report(Request $request){

        $contracts = Expense::select('contract_id')->when($request->start_date, function($query, $start_date){
            return $query->whereDate('date', '>=', $start_date);
            })->when($request->end_date, function($query, $end_date){
            return $query->whereDate('date', '<=', $end_date);
            })->groupBy('contract_id')->get();


        return view('expenses.report', compact('contracts'));
    }

    public function personnel(Request $request){
        $expenses = Expense::active()->where('description', 'like', 'Pago a personal:%')
        ->when($request->description, function($query, $description){
            return $query->where('description', 'like', '%'.$description.'%');
        })->when($request->start_date, function($query, $start_date){
            return $query->whereDate('date', '>=', $start_date);
        })->when($request->end_date, function($query, $end_date){
            return $query->whereDate('date', '<=', $end_date);
        })->orderBy('id', 'desc');

        $total = $expenses->sum('amount');
        $expenses = $expenses->paginate(20);

        $contracts = Contract::active()->orderBy('id', 'desc')->get();
        $payment_methods = PaymentMethod::where('name', 'not like', '%(Inactivo)%')->get();
        return view('expenses.personnel', compact('contracts', 'payment_methods', 'expenses', 'total'));
    }

    public function pdf(Expense $expense)
    {
        $contract = $expense->contract;
        
        $personnelExpenses = Expense::active()
            ->where('contract_id', $contract->id)
            ->where('description', 'like', 'Pago a personal:%')
            ->get();

        // Get functions from contract json
        $contractEmployees = is_array(json_decode($contract->employees)) ? json_decode($contract->employees) : [];
        $functionsMap = [];
        foreach($contractEmployees as $ce) {
            $functionsMap[trim(mb_strtolower($ce->name))] = $ce->function;
        }

        $fpdf = new Fpdf();
        $fpdf->SetMargins(20, 20, 20);
        $fpdf->AddPage();
        $fpdf->AddFont('Montserrat', '');
        $fpdf->AddFont('Montserrat', 'B');

        // Main Title
        \Carbon\Carbon::setLocale('es');
        $dateStr = optional($contract->event_date)->translatedFormat('d \D\E F') ?? '';
        $title = mb_strtoupper('PAGO PERSONAL ' . optional($contract->package)->name . ' ' . $dateStr, 'UTF-8');
        
        $fpdf->SetFont('Montserrat', 'B', 14);
        $fpdf->SetTextColor(0, 0, 0);
        // We will make a full width cell for title with light gray background
        $fpdf->SetFillColor(230, 230, 230);
        $fpdf->Cell(170, 10, utf8_decode($title), 1, 1, 'C', true);

        // Table Header
        $fpdf->SetFont('Montserrat', 'B', 12);
        $fpdf->Cell(60, 8, 'NOMBRE', 1, 0, 'C', true);
        $fpdf->Cell(80, 8, 'FUNCION', 1, 0, 'C', true);
        $fpdf->Cell(30, 8, 'PAGO', 1, 1, 'C', true);

        // Table Rows
        $fpdf->SetFont('Montserrat', '', 11);
        $fpdf->SetFillColor(255, 255, 255);
        $total = 0;

        foreach($personnelExpenses as $pe) {
            $name = mb_strtoupper($pe->provider, 'UTF-8');
            $func = $functionsMap[trim(mb_strtolower($pe->provider))] ?? 'PERSONAL';
            $func = mb_strtoupper($func, 'UTF-8');
            
            $fpdf->Cell(60, 8, utf8_decode(' ' . $name), 1, 0, 'L');
            $fpdf->Cell(80, 8, utf8_decode(' ' . $func), 1, 0, 'L');
            $fpdf->Cell(30, 8, $pe->amount, 1, 1, 'C');
            $total += $pe->amount;
        }

        // Total Row
        $fpdf->SetFont('Montserrat', 'B', 12);
        $fpdf->SetFillColor(230, 230, 230);
        $fpdf->Cell(140, 8, 'TOTAL', 1, 0, 'C', true);
        $fpdf->Cell(30, 8, number_format($total, 2, '.', ''), 1, 1, 'C', true);

        $fpdf->Output('I', 'Pagos_Personal_' . $contract->id . '.pdf');
    }
}
