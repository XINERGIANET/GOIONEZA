<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Codedge\Fpdf\Fpdf\Fpdf;
use Carbon\Carbon;
use App\Models\Contract;
use App\Models\Location;
use App\Models\EventType;
use App\Models\Package;
use App\Models\PaymentMethod;
use App\Models\Extra;
use App\Models\Employee;
use App\Models\Payment;
use App\Models\Expense;

class ContractController extends Controller
{
    public function index(Request $request)
    {
        $contracts = Contract::active()->when($request->name, function ($query, $name) {
            return $query->where(function ($q) use ($name) {
                    $q->where('name', 'like', '%' . $name . '%')
                        ->orWhere('code', 'like', '%' . $name . '%');
                }
                );
            })->when($request->start_date, function ($query, $start_date) {
            return $query->whereDate('event_date', '>=', $start_date);
        })->when($request->end_date, function ($query, $end_date) {
            return $query->whereDate('event_date', '<=', $end_date);
        });
        $total = $contracts->sum('total');
        $contracts = $contracts->orderBy('id', 'desc')->paginate(20);
        $locations = Location::active()->get();
        $event_types = EventType::active()->get();
        $packages = Package::active()->get();
        $extras = Extra::active()->get();
        $employees = Employee::active()->get();
        $payment_methods = PaymentMethod::all();
        $commissions = \App\Models\Commission::all();
        return view('contracts.index', compact('contracts', 'locations', 'event_types', 'packages', 'extras', 'employees', 'payment_methods', 'total', 'commissions'));
    }

    public function charges(Request $request)
    {
        $contracts = Contract::active()->when($request->name, function ($query, $name) {
            return $query->where(function ($q) use ($name) {
                    $q->where('name', 'like', '%' . $name . '%')
                        ->orWhere('code', 'like', '%' . $name . '%');
                }
                );
            })->when($request->start_date, function ($query, $start_date) {
            return $query->whereDate('debt_payment_date', '>=', $start_date);
        })->when($request->end_date, function ($query, $end_date) {
            return $query->whereDate('debt_payment_date', '<=', $end_date);
        })->pending();
        $total = $contracts->sum('debt');
        $contracts = $contracts->orderBy('id', 'desc')->paginate(20);
        $payment_methods = PaymentMethod::all();
        return view('contracts.charges', compact('contracts', 'payment_methods', 'total'));
    }

    public function store(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'clients' => 'required|array|min:1',
            'clients.*.document' => 'required',
            'clients.*.name' => 'required',
            'location_id' => 'required',
            'event_type_id' => 'required',
            'event_date' => 'required|date',
            'event_time' => 'required|date_format:H:i',
            'event_duration' => 'required|integer|min:1',
            'package_id' => 'required',
            'people_number' => 'required|integer',
            'discount_type' => 'required',
            'discount' => 'required|numeric',
            'initial_payment' => 'required|numeric',
            'payment_type' => 'required',
            'payment_method_id' => 'required',
            'debt_payment_date' => 'required|date'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'error' => $validator->errors()->first()
            ]);
        }

        $package = Package::find($request->package_id);

        $total = floatval($package->price) * intval($request->people_number);

        $extras = [];
        $employees = [];

        if (is_array($request->extra)) {
            foreach ($request->extra as $id) {
                $extra = Extra::find($id);

                $extras[] = [
                    'id' => $extra->id,
                    'price' => $extra->price
                ];

                $total += floatval($extra->price);
            }
        }

        if (is_array($request->employee)) {
            foreach ($request->employee as $id) {
                $employee = Employee::find($id);

                $employees[] = [
                    'id' => $employee->id,
                    'name' => $employee->name,
                    'job' => $employee->job,
                    'function' => $employee->function,
                    'start' => '',
                    'end' => ''
                ];
            }
        }

        $discount = 0;

        if ($request->discount_type == 'Persona') {
            $discount = floatval($request->discount) * intval($request->people_number);
        }
        elseif ($request->discount_type == 'Evento') {
            $discount = floatval($request->discount);
        }

        $total = $total - $discount;


        $event_time = Carbon::parse($request->event_time);
        $event_end = $event_time->addHours($request->event_duration);

        if ($request->payment_type == 'Contado') {

            $initial_payment = 0;
            $debt = 0;
            $paid = 1;


        }
        elseif ($request->payment_type == 'Crédito') {
            $initial_payment = floatval($request->initial_payment);
            $debt = $total - $initial_payment;
            $paid = 0;

        }


        $first_client = $request->clients[0];

        $contract = Contract::create([
            'document' => $first_client['document'],
            'name' => $first_client['name'],
            'business_document' => $first_client['business_document'] ?? null,
            'business_name' => $first_client['business_name'] ?? null,
            'phone' => $first_client['phone'] ?? null,
            'email' => $first_client['email'] ?? null,
            'clients' => json_encode($request->clients),
            'location_id' => $request->location_id,
            'event_type_id' => $request->event_type_id,
            'event_date' => $request->event_date,
            'event_time' => $request->event_time,
            'event_duration' => $request->event_duration,
            'event_end' => $event_end->format('H:i'),
            'package_id' => $request->package_id,
            'people_number' => $request->people_number,
            'extras' => json_encode($extras),
            'total' => $total,
            'discount_type' => $request->discount_type,
            'discount' => $discount,
            'initial_payment' => $initial_payment,
            'payment_type' => $request->payment_type,
            'payment_method_id' => $request->payment_method_id,
            'date' => now(),
            'debt' => $debt,
            'debt_payment_date' => $request->debt_payment_date,
            'paid' => $paid,
            'commission_id' => $request->has_commission ? $request->commission_id : null,
            'commission_amount' => $request->has_commission ? $request->commission_amount : null
        ]);

        $code = 'C-' . str_pad($contract->id, 3, '0', STR_PAD_LEFT);
        $contract->update(['code' => $code]);

        if ($request->has_commission && $request->commission_id && $request->commission_amount) {
            $commissionModel = \App\Models\Commission::find($request->commission_id);
            Expense::create([
                'contract_id' => $contract->id,
                'description' => 'Comisión por contrato ' . $code,
                'responsible' => auth()->user()->name ?? 'Sistema',
                'voucher' => 'Ticket',
                'voucher_number' => '-',
                'provider' => $commissionModel ? $commissionModel->names : 'Comisión',
                'amount' => $request->commission_amount,
                'payment_method_id' => $request->payment_method_id,
                'date' => now(),
                'deleted' => 0
            ]);
        }

        if ($request->payment_type == 'Contado') {
            Payment::create([
                'contract_id' => $contract->id,
                'operation_number' => '',
                'amount' => $total,
                'payment_method_id' => $request->payment_method_id,
                'date' => now()
            ]);
        }

        return response()->json([
            'status' => true
        ]);
    }

    public function edit(Request $request, Contract $contract)
    {
        return response()->json([
            'id' => $contract->id,
            'clients' => json_decode($contract->clients),
            'document' => $contract->document,
            'name' => $contract->name,
            'business_document' => $contract->business_document,
            'business_name' => $contract->business_name,
            'phone' => $contract->phone,
            'email' => $contract->email,
            'location_id' => $contract->location_id,
            'event_type_id' => $contract->event_type_id,
            'people_number' => $contract->people_number,
            'event_date' => $contract->event_date->format('Y-m-d'),
            'event_time' => $contract->event_time->format('H:i')
        ]);
    }

    public function update(Request $request, Contract $contract)
    {
        $validator = Validator::make($request->all(), [
            'clients' => 'required|array|min:1',
            'clients.*.document' => 'required',
            'clients.*.name' => 'required',
            'location_id' => 'required',
            'event_type_id' => 'required',
            'people_number' => 'required|integer',
            'event_date' => 'required|date',
            'event_time' => 'required|date_format:H:i',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'error' => $validator->errors()->first()
            ]);
        }

        $first_client = $request->clients[0];

        $contract->document = $first_client['document'];
        $contract->name = $first_client['name'];
        $contract->business_document = $first_client['business_document'] ?? null;
        $contract->business_name = $first_client['business_name'] ?? null;
        $contract->phone = $first_client['phone'] ?? null;
        $contract->email = $first_client['email'] ?? null;
        $contract->clients = json_encode($request->clients);
        $contract->location_id = $request->location_id;
        $contract->event_type_id = $request->event_type_id;
        $contract->event_date = $request->event_date;
        $contract->event_time = $request->event_time;

        if ($request->people_number != $contract->people_number) {

            $total = 0;

            $package = $contract->package;

            $total = floatval($package->price) * intval($request->people_number);

            $extras = is_array(json_decode($contract->extras)) ? json_decode($contract->extras) : [];

            if (is_array($extras)) {
                foreach ($extras as $extra) {

                    $total += floatval($extra->price);
                }
            }

            $total = $total - floatval($contract->discount);

            $payments = $contract->payments->sum('amount');

            $debt = $total - floatval($contract->initial_payment) - floatval($payments);

            $contract->people_number = $request->people_number;
            $contract->total = $total;
            $contract->debt = $debt;

        }

        $contract->save();

        return response()->json([
            'status' => true
        ]);
    }

    public function payment(Request $request, Contract $contract)
    {
        $validator = Validator::make($request->all(), [
            'operation_number' => 'required',
            'amount' => 'required|numeric',
            'payment_method_id' => 'required',
            'date' => 'required|date',
        ]);

        $validator->after(function ($validator) use ($request, $contract) {
            if (floatval($request->amount) > floatval($contract->debt)) {
                $validator->errors()->add('amount', 'El monto debe ser menor o igual a la deuda');
            }
        });

        if ($validator->fails()) {
            return response()->json([
                'status' => false,
                'error' => $validator->errors()->first()
            ]);
        }

        Payment::create([
            'contract_id' => $contract->id,
            'operation_number' => $request->operation_number,
            'amount' => $request->amount,
            'payment_method_id' => $request->payment_method_id,
            'date' => $request->date
        ]);

        $debt = floatval($contract->debt) - floatval($request->amount);
        $paid = $debt == 0 ? 1 : 0;

        $contract->update([
            'debt' => $debt,
            'paid' => $paid
        ]);

        return response()->json([
            'status' => true
        ]);
    }

    public function payments(Request $request, Contract $contract)
    {
        $payments = $contract->payments()->with('payment_method')->orderBy('date', 'desc')->get()->map(function ($payment) {
            return [
            'operation_number' => $payment->operation_number,
            'amount' => $payment->amount,
            'payment_method' => optional($payment->payment_method)->name,
            'date' => $payment->date->format('d/m/Y')
            ];
        });
        return response()->json([
            'status' => true,
            'payments' => $payments
        ]);
    }

    public function destroy(Request $request, Contract $contract)
    {

        $pin_db = DB::table('settings')->pluck('pin')->first();

        if ($request->pin == $pin_db) {
            $contract->update([
                'deleted' => 1
            ]);
            $contract->delete();

            return response()->json([
                'status' => true
            ]);

        }
        else {

            return response()->json([
                'status' => false,
                'error' => 'El PIN es incorrecto.'
            ]);

        }


    }

    public function extra(Request $request, Contract $contract)
    {


        $extras = is_array(json_decode($contract->extras)) ? json_decode($contract->extras) : [];

        $total = 0;

        if (is_array($request->extra)) {
            foreach ($request->extra as $id) {
                $extra = Extra::find($id);

                $extras[] = [
                    'id' => $extra->id,
                    'price' => $extra->price
                ];

                $total += floatval($extra->price);
            }
        }

        $contract->update([
            'extras' => json_encode($extras),
            'total' => floatval($contract->total) + $total,
            'debt' => floatval($contract->debt) + $total,
            'paid' => 0
        ]);

        return response()->json([
            'status' => true
        ]);
    }

    public function employees(Request $request, Contract $contract)
    {
        $employees = is_array(json_decode($contract->employees)) ? json_decode($contract->employees) : [];

        return response()->json(array_column($employees, 'id'));
    }

    public function employee(Request $request, Contract $contract)
    {


        $employees = [];

        if (is_array($request->employee)) {
            foreach ($request->employee as $id) {

                $employee = Employee::find($id);

                $employees[] = [
                    'id' => $employee->id,
                    'name' => $employee->name,
                    'job' => $employee->job,
                    'function' => $employee->function,
                    'start' => '',
                    'end' => ''
                ];
            }
        }

        $contract->update([
            'employees' => json_encode($employees)
        ]);

        return response()->json([
            'status' => true
        ]);
    }

    public function schedules(Request $request, Contract $contract)
    {

        $employees = is_array(json_decode($contract->employees)) ? json_decode($contract->employees) : [];

        return response()->json([
            'date' => $contract->event_date->format('d/m/Y') . ' ' . $contract->event_time->format('H:i') . ' - ' . $contract->event_end->format('H:i'),
            'employees' => $employees
        ]);
    }

    public function schedule(Request $request, Contract $contract)
    {
        $employees = is_array(json_decode($contract->employees)) ? json_decode($contract->employees) : [];

        for ($i = 0; $i < count($request->start); $i++) {
            $employees[$i]->start = $request->start[$i];
            $employees[$i]->end = $request->end[$i];
        }

        $contract->update([
            'employees' => json_encode($employees)
        ]);

        return response()->json([
            'status' => true
        ]);
    }

    public function total(Request $request, Contract $contract)
    {
        return response()->json(['id' => $contract->id, 'total' => $contract->total]);
    }

    public function updateTotal(Request $request, Contract $contract)
    {

        $validator = Validator::make($request->all(), [
            'total' => 'required|numeric',
        ]);

        $total = $request->total;

        $payments = $contract->payments->sum('amount');

        $debt = $total - floatval($contract->initial_payment) - floatval($payments);

        $contract->total = $total;
        $contract->debt = $debt;
        $contract->discount = 0;
        $contract->save();

        return response()->json([
            'status' => true
        ]);
    }

    public function pdf(Contract $contract)
    {
        $fpdf = new Fpdf();
        $fpdf->SetMargins(25, 25, 25);
        $fpdf->AddPage();

        $fpdf->AddFont('Montserrat', '');
        $fpdf->AddFont('Montserrat', 'B');

        $fpdf->SetFont('Montserrat', 'B', 14);

        $fpdf->MultiCell(160, 6, utf8_decode('CONTRATO PARA EVENTO QUINTA FERNANDINI RECEPCIONES'), 0, 'C');

        $fpdf->Ln(15);

        $fpdf->SetFont('Montserrat', '', 11);
        
        $meses = ["Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"];
        $dia = $contract->date->format('d');
        $mes = $meses[intval($contract->date->format('m')) - 1];
        $anio = $contract->date->format('Y');

        $fpdf->MultiCell(160, 6, utf8_decode("En Chiclayo, a los $dia días del mes de $mes del $anio"), 0, 'L');
        $fpdf->Ln(6);

        $fpdf->SetFont('Montserrat', 'U', 11);
        $fpdf->Cell(160, 6, 'REUNIDAS AMBAS PARTES:', 0, 1);
        $fpdf->Ln(2);

        $fpdf->SetFont('Montserrat', '', 11);
        $fpdf->Cell(160, 6, 'Como prestador del servicio:', 0, 1);
        $fpdf->Ln(2);

        $fpdf->SetFont('Montserrat', 'B', 11);
        $fpdf->Cell(160, 6, utf8_decode('LIDIA UCEDA CUSTODIO'), 0, 1);
        $fpdf->SetFont('Montserrat', '', 11);
        $fpdf->MultiCell(160, 6, utf8_decode('identificada con DNI N° 41927732, con domicilio en CALLE LOS DIAMANTES 151 - SECTOR GARITA - CARRETERA A PIMENTEL, (como referencia FRENTE AL CONTRY CLUB LOS ALGARROBOS), con número de celular 976715568 - 983865182.'), 0, 'J');
        $fpdf->Ln(6);

        $fpdf->Cell(160, 6, 'Como Cliente - Usuario:', 0, 1);
        $fpdf->Ln(2);

        // Extract multiple clients
        $clientsArray = json_decode($contract->clients, true);
        if (is_array($clientsArray) && count($clientsArray) > 0) {
            $clientNames = [];
            $clientDocs = [];
            $clientPhones = [];
            foreach ($clientsArray as $c) {
                if(!empty($c['name'])) $clientNames[] = $c['name'];
                if(!empty($c['document'])) $clientDocs[] = $c['document'];
                if(!empty($c['phone'])) $clientPhones[] = $c['phone'];
            }
            $namesStr = count($clientNames) > 1 ? implode(', ', array_slice($clientNames, 0, -1)) . ' y ' . end($clientNames) : implode('', $clientNames);
            $docsStr = count($clientDocs) > 1 ? implode(', ', array_slice($clientDocs, 0, -1)) . ' y ' . end($clientDocs) : implode('', $clientDocs);
            $phonesStr = count($clientPhones) > 1 ? implode(', ', array_slice($clientPhones, 0, -1)) . ' y ' . end($clientPhones) : implode('', $clientPhones);
        } else {
            $namesStr = $contract->name;
            $docsStr = $contract->document;
            $phonesStr = $contract->phone;
        }

        $fpdf->MultiCell(160, 6, utf8_decode('La (El) Señora(r) ' . $namesStr . ', identificada(o) con DNI N° ' . $docsStr . ', y número de celular ' . $phonesStr . ', acuerdan celebrar un contrato para la realización de eventos en base a las siguientes cláusulas:'), 0, 'J');
        $fpdf->Ln(6);

        $fpdf->SetFont('Montserrat', 'B', 11);
        $fpdf->Cell(160, 6, 'PRIMERA:', 0, 1);
        $fpdf->SetFont('Montserrat', '', 11);
        $fpdf->MultiCell(160, 6, utf8_decode('Por el presente contrato el cliente manifiesta su necesidad de contratar los servicios de la prestadora del servicio para la realización de un evento consistente en la celebración del evento en el Local "Quinta Fernandini", el ' . $contract->event_date->format('d/m/Y') . '; quedará reservado el evento que tiene una capacidad de ' . $contract->people_number . ' personas con la duración de ' . $contract->event_duration . ' horas, desde las ' . optional($contract->event_time)->format('h:i a') . ' hasta las ' . optional($contract->event_end)->format('h:i a') . '.'), 0, 'J');
        $fpdf->Ln(4);

        $fpdf->SetFont('Montserrat', 'B', 11);
        $fpdf->Cell(160, 6, 'SEGUNDA:', 0, 1);
        $fpdf->SetFont('Montserrat', '', 11);
        $fpdf->MultiCell(160, 6, utf8_decode('Los invitados serán un total de ' . $contract->people_number . ' adultos (los niños mayores a 3 años pagan cubierto). Este número podrá variar hasta 8 días hábiles antes de la fecha citada para la celebración del evento. En caso de sobrepasar los asistentes acordados se pagarán los cubiertos de los mismos con un 10% adicional por no haber sido comunicado. El precio del cubierto es S/' . number_format(optional($contract->package)->price, 2) . ' según el paquete personalizado, haciendo un total de S/' . number_format($contract->total, 2) . '. A la firma del contrato se entrega la suma de S/' . number_format($contract->initial_payment, 2) . ' quedando un saldo de S/' . number_format($contract->debt, 2) . ' que debe ser cancelado una semana antes del día del evento.'), 0, 'J');
        $fpdf->Ln(4);

        $fpdf->SetFont('Montserrat', 'B', 11);
        $fpdf->Cell(160, 6, 'TERCERA:', 0, 1);
        $fpdf->SetFont('Montserrat', '', 11);
        $fpdf->MultiCell(160, 6, utf8_decode('El prestador de servicios cubre el siguiente paquete: ' . optional($contract->package)->name . '.'), 0, 'J');
        $fpdf->Ln(4);

        $fpdf->SetFont('Montserrat', 'B', 11);
        $fpdf->Cell(160, 6, 'CUARTA:', 0, 1);
        $fpdf->SetFont('Montserrat', '', 11);
        $fpdf->MultiCell(160, 6, utf8_decode('El cliente está en la obligación de pagar una garantía de S/500 por los servicios y suministros del local, y en caso de que no pase absolutamente nada, la garantía será devuelta en un plazo de 7 días hábiles.'), 0, 'J');
        $fpdf->Ln(2);
        $b = chr(149);
        $text4 = utf8_decode("Costo de cristalería:\n") .
                 "  $b " . utf8_decode("Vaso / copa larga o globo de cristal S/7.00\n") .
                 "  $b " . utf8_decode("Jarras / hieleras / ceniceros S/35.00\n") .
                 "  $b " . utf8_decode("Botellas de cerveza S/5.00\n") .
                 "  $b " . utf8_decode("Puertas / espejos / adornos / floreros / mantel / servilletas se calculará según el daño ocurrido.\n") .
                 "  $b " . utf8_decode("Luces lineales (algunas veces son los niños que las manipulan) S/100.00");
        $fpdf->MultiCell(160, 6, $text4, 0, 'J');
        $fpdf->Ln(4);

        $fpdf->SetFont('Montserrat', 'B', 11);
        $fpdf->Cell(160, 6, 'QUINTA:', 0, 1);
        $fpdf->SetFont('Montserrat', '', 11);
        $text5 = utf8_decode("En caso de desear servicios extras, el cliente escoge en función de su interés los siguientes citados:\n") .
                 "  $b " . utf8_decode("Mozo adicional S/120.00\n") .
                 "  $b " . utf8_decode("Hora adicional de servicios S/500.00\n") .
                 "  $b " . utf8_decode("Pantalla led (durante el evento) S/1200.00\n") .
                 "  $b " . utf8_decode("Hora loca Básica con Valdiviezo S/700.00");
        $fpdf->MultiCell(160, 6, $text5, 0, 'J');
        $fpdf->Ln(4);

        $fpdf->SetFont('Montserrat', 'B', 11);
        $fpdf->Cell(160, 6, 'SEXTA:', 0, 1);
        $fpdf->SetFont('Montserrat', '', 11);
        $fpdf->MultiCell(160, 6, utf8_decode("El importe total del evento/banquete se abonará de la siguiente manera: un 10% a la firma inicial y acuerdo de dicho contrato; un 90% la semana antes de la realización del evento."), 0, 'J');
        $fpdf->Ln(4);

        $fpdf->SetFont('Montserrat', 'B', 11);
        $fpdf->Cell(160, 6, utf8_decode('SÉPTIMA:'), 0, 1);
        $fpdf->SetFont('Montserrat', '', 11);
        $fpdf->MultiCell(160, 6, utf8_decode("En el caso de contratar proveedores de orquesta, este debe contar con un motor adecuado para su funcionamiento durante el evento, se prohíbe estrictamente usar la energía o punto de luz del local para la orquesta. Serán revisadas y deben contar con un buen cableado para evitar accidentes."), 0, 'J');
        $fpdf->Ln(4);

        $fpdf->SetFont('Montserrat', 'B', 11);
        $fpdf->Cell(160, 6, 'OCTAVA:', 0, 1);
        $fpdf->SetFont('Montserrat', '', 11);
        $fpdf->MultiCell(160, 6, utf8_decode('Respecto a la política de cancelación del evento, no hay devolución del importe abonado para separación de la fecha, si fuera inferior a los días citados no existirá ningún tipo de retribución por parte de la empresa encargada de organizar el evento; en caso de no presentarse en el mismo día de la celebración tampoco existirá ningún tipo de devolución.'), 0, 'J');
        $fpdf->Ln(4);

        $fpdf->SetFont('Montserrat', 'B', 11);
        $fpdf->Cell(160, 6, 'NOVENA:', 0, 1);
        $fpdf->SetFont('Montserrat', '', 11);
        $fpdf->MultiCell(160, 6, utf8_decode('En caso de desperfectos en las instalaciones de la empresa se hará una previa valoración de estos con los encargados y si se atribuyen daños graves los clientes deberán de pagarlos en su totalidad.'), 0, 'J');
        $fpdf->Ln(4);

        $fpdf->SetFont('Montserrat', 'B', 11);
        $fpdf->Cell(160, 6, 'ANEXO 1:', 0, 1);
        $fpdf->SetFont('Montserrat', '', 11);
        $fpdf->MultiCell(160, 6, utf8_decode('En caso de ser EL CLIENTE el encargado de llevar servicios extras, la empresa se exime de cualquier fallo técnico, pues serán ellos los únicos responsables de sus actos.'), 0, 'J');
        $fpdf->Ln(4);

        $fpdf->SetFont('Montserrat', 'B', 11);
        $fpdf->Cell(160, 6, 'ANEXO 2:', 0, 1);
        $fpdf->SetFont('Montserrat', '', 11);
        $fpdf->MultiCell(160, 6, utf8_decode('En caso de que EL CLIENTE quiera encargarse de la cerveza, deberá pagar un derecho de corcho libre de S/400.00 y 20 cajas permitidas, además de contratar un mozo y abonar S/100.00 por alquiler de congeladora.'), 0, 'J');
        $fpdf->Ln(15);

        $fpdf->Cell(70, 5, '__________________________', 0, 0, 'C');
        $fpdf->Cell(20, 5);
        $fpdf->Cell(70, 5, '__________________________', 0, 1, 'C');

        $fpdf->SetFont('Montserrat', 'B', 11);
        $fpdf->Cell(70, 5, 'EL CLIENTE', 0, 0, 'C');
        $fpdf->Cell(20, 5);
        $fpdf->Cell(70, 5, 'LA PRESTADORA', 0, 1, 'C');

        $filename = 'Contrato_' . $contract->id . '.pdf';

        $fpdf->Output('I', $filename);
    }

    public function pdf2(Contract $contract)
    {
        $fpdf = new Fpdf();

        $fpdf->AddPage();

        $fpdf->AddFont('Montserrat', '');
        $fpdf->AddFont('Montserrat', 'B');

        $fpdf->Image(public_path('assets/images/logonew2.png'), 85, 15, 45);

        $fpdf->Ln(30);

        $fpdf->SetFillColor(251, 202, 172);

        $fpdf->SetFont('Montserrat', 'B', 10);
        $fpdf->Cell(60, 8, utf8_decode('TIPO DE EVENTO'), 1, 0, 'C', 1);
        $fpdf->SetFont('Montserrat', '', 10);
        $fpdf->Cell(130, 8, utf8_decode(optional($contract->event_type)->name), 1, 1);

        $fpdf->SetFont('Montserrat', 'B', 10);
        $fpdf->Cell(60, 8, utf8_decode('FECHA DE EVENTO'), 1, 0, 'C', 1);
        $fpdf->SetFont('Montserrat', '', 10);
        $fpdf->Cell(60, 8, utf8_decode($contract->event_date->format('d/m/Y')), 1);
        $fpdf->SetFont('Montserrat', 'B', 10);
        $fpdf->Cell(35, 8, utf8_decode('HORA INICIO'), 1, 0, 'C', 1);
        $fpdf->SetFont('Montserrat', '', 10);
        $fpdf->Cell(35, 8, utf8_decode($contract->event_time->format('h:i a')), 1, 1);

        $fpdf->SetFont('Montserrat', 'B', 10);
        $fpdf->Cell(60, 8, utf8_decode('CARGO DEL EVENTO'), 1, 0, 'C', 1);
        $fpdf->SetFont('Montserrat', '', 10);
        $fpdf->Cell(60, 8, utf8_decode('LIDIA UCEDA'), 1);
        $fpdf->SetFont('Montserrat', 'B', 10);
        $fpdf->Cell(35, 8, utf8_decode('HORA TERMINO'), 1, 0, 'C', 1);
        $fpdf->SetFont('Montserrat', '', 10);
        $fpdf->Cell(35, 8, utf8_decode($contract->event_end->format('h:i a')), 1, 1);

        $fpdf->SetFont('Montserrat', 'B', 10);
        $fpdf->Cell(60, 8, utf8_decode('DESCRIPCIÓN DEL EVENTO'), 1, 0, 'C', 1);
        $fpdf->SetFont('Montserrat', '', 10);
        $fpdf->Cell(130, 8, utf8_decode(optional($contract->package)->name), 1, 1);

        $fpdf->SetFont('Montserrat', 'B', 10);
        $fpdf->Cell(190, 8, utf8_decode('PERSONAL EN RECEPCIÓN'), 1, 1, 'C', 1);

        $fpdf->SetFont('Montserrat', 'B', 10);
        $fpdf->Cell(20, 8, utf8_decode('N°'), 1, 0, 'C', 1);
        $fpdf->Cell(40, 8, utf8_decode('CARGO'), 1, 0, 'C', 1);
        $fpdf->Cell(60, 8, utf8_decode('NOMBRE'), 1, 0, 'C', 1);
        $fpdf->Cell(35, 8, utf8_decode('ENTRADA'), 1, 0, 'C', 1);
        $fpdf->Cell(35, 8, utf8_decode('SALIDA'), 1, 1, 'C', 1);

        $fpdf->SetFont('Montserrat', '', 8);

        $employees = json_decode($contract->employees) ?? [];

        $count = 0;

        foreach ($employees as $employee) {

            $fpdf->Cell(20, 5, "1", 1, 0, 'C');
            $fpdf->SetFont('Montserrat', 'B', 8);
            $fpdf->Cell(40, 5, utf8_decode($employee->job), 1, 0, 'C', 1);
            $fpdf->SetFont('Montserrat', '', 8);
            $fpdf->Cell(60, 5, utf8_decode(explode(" ", $employee->name)[0]), 1, 0, 'C');
            $fpdf->Cell(35, 5, utf8_decode($employee->start), 1, 0, 'C');
            $fpdf->Cell(35, 5, utf8_decode($employee->end), 1, 1, 'C');
            $fpdf->MultiCell(190, 5, utf8_decode("Función: " . $employee->function), 1);

            $count++;

        }

        $fpdf->SetFont('Montserrat', 'B', 8);

        $fpdf->Cell(20, 5, $count, 1, 0, 'C');
        $fpdf->Cell(170, 5, utf8_decode("Total de personal de recepción"), 1, 0, 'C');

        $fpdf->Ln();

        $fpdf->Output();
    }
}
