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
        $contracts = $contracts->paginate(20);
        $locations = Location::active()->get();
        $event_types = EventType::active()->get();
        $packages = Package::active()->get();
        $extras = Extra::active()->get();
        $employees = Employee::active()->get();
        $payment_methods = PaymentMethod::all();
        return view('contracts.index', compact('contracts', 'locations', 'event_types', 'packages', 'extras', 'employees', 'payment_methods', 'total'));
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
        $contracts = $contracts->paginate(20);
        $payment_methods = PaymentMethod::all();
        return view('contracts.charges', compact('contracts', 'payment_methods', 'total'));
    }

    public function store(Request $request)
    {

        $validator = Validator::make($request->all(), [
            'document' => 'required|integer',
            'name' => 'required',
            'business_document' => 'nullable|integer',
            'phone' => 'nullable|integer',
            'email' => 'nullable|email',
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


        $contract = Contract::create([
            'document' => $request->document,
            'name' => $request->name,
            'business_document' => $request->business_document,
            'business_name' => $request->business_name,
            'phone' => $request->phone,
            'email' => $request->email,
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
            'paid' => $paid
        ]);

        $code = 'C-' . str_pad($contract->id, 3, '0', STR_PAD_LEFT);
        $contract->update(['code' => $code]);

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
            'document' => 'required|integer',
            'name' => 'required',
            'business_document' => 'nullable|integer',
            'phone' => 'nullable|integer',
            'email' => 'nullable|email',
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

        $contract->document = $request->document;
        $contract->name = $request->name;
        $contract->business_document = $request->business_document;
        $contract->business_name = $request->business_name;
        $contract->phone = $request->phone;
        $contract->email = $request->email;
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

        $fpdf->AddPage();

        $fpdf->AddFont('Montserrat', '');
        $fpdf->AddFont('Montserrat', 'B');

        $fpdf->Image(asset('assets/images/logonew2.png'), 15, 15, 45);
        $fpdf->Ln(20);

        $fpdf->SetFont('Montserrat', 'B', 14);

        $fpdf->Cell(190, 5, 'CONTRATO DE EVENTO', 0, 1, 'C');

        $fpdf->Ln();

        $fpdf->SetFont('Montserrat', '', 12);

        $fpdf->Cell(190, 5, 'Chiclayo, ' . $contract->date->format('d/m/Y'), 0, 1);

        $fpdf->Ln();

        $fpdf->SetFont('Montserrat', 'B', 12);

        $fpdf->Cell(95, 5, 'REUNIDAS AMBAS PARTES', 0, 0);

        $fpdf->SetFont('Montserrat', 'B', 14);
        $fpdf->Cell(95, 5, utf8_decode('CÓDIGO: ' . $contract->code), 0, 1, 'R');

        $fpdf->Ln();

        $fpdf->SetFont('Montserrat', '', 12);

        $fpdf->Cell(190, 5, 'Como prestador del servicio:', 0, 1);

        $fpdf->Ln();

        $fpdf->MultiCell(190, 5, utf8_decode('Sra Lidia Uceda Custodio con DNI 41927732 Domicilio en Chiclayo, Prolongación Pedro Cieza de León Sub Lt C6, Ref al costado del colegio ELIM Con teléfono 976715568.'), 0, 1);

        $fpdf->Ln();

        $fpdf->Cell(190, 5, 'Como cliente:', 0, 1);

        $fpdf->Ln();

        $fpdf->MultiCell(190, 5, utf8_decode('Sr.(a) ' . $contract->name . ' con DNI ' . $contract->document . ', teléfono ' . $contract->phone . '.'), 0, 1);

        $fpdf->Ln();

        $fpdf->Cell(190, 5, utf8_decode('Ambas partes acuerdan celebrar el presente contrato con las siguientes:'), 0, 1);

        $fpdf->Ln();

        $fpdf->SetFont('Montserrat', 'B', 12);

        $fpdf->Cell(190, 5, utf8_decode('CLÁUSULAS'), 0, 1);

        $fpdf->Ln();

        $fpdf->SetFont('Montserrat', '', 12);

        $fpdf->Cell(190, 5, 'PRIMERA:', 0, 1);

        $fpdf->Ln();

        $fpdf->MultiCell(190, 5, utf8_decode('Por el presente contrato ambas partes acuerdan la celebración del evento en el Local "Quinta Fernandini", el ' . $contract->event_date->format('d/m/Y') . '; quedará reservado, el evento que tiene una capacidad de ' . $contract->people_number . ' con la duración de ' . $contract->event_duration . ' horas, desde las ' . optional($contract->event_time)->format('H:i a') . ' hasta ' . optional($contract->event_end)->format('H:i a')), 0, 1);

        $fpdf->Ln();

        $fpdf->Cell(190, 5, 'SEGUNDA:', 0, 1);

        $fpdf->Ln();

        $fpdf->MultiCell(190, 5, utf8_decode('Los invitados serán un total de ' . $contract->people_number . ' adultos (los niños mayores a 3 años pagan cubierto)'), 0, 1);

        $fpdf->Ln();

        $fpdf->MultiCell(190, 5, utf8_decode('Este podrá variar hasta 8 días hábiles antes de la fecha citada para la celebración del evento, en caso de sobrepasar los asistentes acordados se pagarán los cubiertos de los mismos con un 10% adicional por no haber sido comunicado.'), 0, 1);

        $fpdf->Ln();

        $fpdf->MultiCell(190, 5, utf8_decode('El precio del cubierto es S/' . number_format(optional($contract->package)->price, 2) . ' según el paquete  personalizado  haciendo un total S/' . number_format($contract->total, 2) . ' a la firma del contrato se entrega la suma de S/' . number_format($contract->initial_payment, 2) . ' quedando un saldo de S/' . number_format($contract->debt, 2) . ' soles que deben ser cancelados una semana antes del día evento.'), 0, 1);

        $fpdf->Ln();

        $fpdf->Cell(190, 5, 'TERCERA:', 0, 1);

        $fpdf->Ln();

        $fpdf->Cell(190, 5, utf8_decode('El prestador de servicios cubre las siguiente: Paquete personalizado'), 0, 1);

        $fpdf->Ln();

        $fpdf->MultiCell(190, 5, utf8_decode(optional($contract->package)->description), 0, 1);


        $fpdf->Ln();

        $fpdf->Cell(190, 5, 'CUARTA:', 0, 1);

        $fpdf->Ln();

        $fpdf->MultiCell(190, 5, utf8_decode('El cliente está en la obligación de pagar una garantía de S/500 por los servicios y suministros del local y en caso que no pase absolutamente nada la garantía será devuelta en un plazo de 7 días hábiles.'), 0, 1);

        $fpdf->Ln();

        $fpdf->MultiCell(190, 5, utf8_decode("Costo de cristalería: \n - Vaso / copa larga o globo de cristal S/7 \n - Jarras/ hieleras/ ceniceros S/35  \n Botellas de cerveza S/5 \n - Puertas / espejos / adornos / floreros / mantel / servilletas se calculará según el daño ocurrido. \n - Luces lineales (algunas veces son los niños que manipulan) S/100"), 0, 1);

        $fpdf->Ln();

        $fpdf->Cell(190, 5, 'QUINTA:', 0, 1);

        $fpdf->Ln();

        $fpdf->MultiCell(190, 5, utf8_decode("En caso de deseo de servicios extras el cliente escoge en función de su interés los siguientes citados,\n - Mozo adicional S/120 \n - Hora adicional de servicios S/500 \n - Pantalla led (durante el evento) S/1200 \n - Hora loca Básica con Valdiviezo S/700"), 0, 1);

        $fpdf->Ln();

        $fpdf->Cell(190, 5, 'SEXTA:', 0, 1);

        $fpdf->Ln();

        $fpdf->MultiCell(190, 5, utf8_decode("El importe total del evento/banquete se abonará de la siguiente manera; un 10% a la firma inicial y acuerdo de dicho contrato; un 90% la semana antes de la realización del evento."), 0, 1);

        $fpdf->Ln();

        $fpdf->Cell(190, 5, 'SEPTIMA:', 0, 1);

        $fpdf->Ln();

        $fpdf->MultiCell(190, 5, utf8_decode("El caso de contratar proveedores de orquesta, este debe contar con un motor adecuado para su funcionamiento durante el evento, se prohíbe estrictamente usar la energía o punto de luz del local para la orquesta."), 0, 1);

        $fpdf->Ln();

        $fpdf->Cell(190, 5, utf8_decode('Serán revisadas y deben contar con un buen cableado para evitar accidentes.'), 0, 1);


        $fpdf->Ln();

        $fpdf->Cell(190, 5, 'OCTAVA:', 0, 1);

        $fpdf->Ln();

        $fpdf->MultiCell(190, 5, utf8_decode('La política de cancelación del evento, no hay devolución del importe abonado para separación de la fecha, si fuera inferior a los días citados no existirá ningún tipo de retribución por parte de la empresa encargada de organizar el evento; en caso de no presentarse en el mismo día de la celebración tampoco existirá ningún tipo de devolución.'), 0, 1);


        $fpdf->Ln();

        $fpdf->Cell(190, 5, 'NOVENA:', 0, 1);

        $fpdf->Ln();

        $fpdf->MultiCell(190, 5, utf8_decode('En caso se desperfectos en las instalaciones de la empresa se hará una previa valoración de estos con los encargados y si se atribuyen de daños graves los clientes deberán de pagarlos en su totalidad.'), 0, 1);

        $fpdf->Ln();

        $fpdf->Cell(190, 5, 'ANEXO 1:', 0, 1);

        $fpdf->Ln();

        $fpdf->MultiCell(190, 5, utf8_decode('En caso de ser EL CLIENTE los encargados de llevar servicios extras la empresa se exime de cualquier fallo técnico, pues serán ellos todos los responsables de sus actos.'), 0, 1);

        $fpdf->Ln();

        $fpdf->Cell(190, 5, 'ANEXO 2:', 0, 1);

        $fpdf->Ln();

        $fpdf->MultiCell(190, 5, utf8_decode('En caso que EL CLIENTE quiera encargarse de la cerveza deberá pagar un derecho de corcho libre 400 soles y 20 cajas permitidas, contratar un mozo y 100 soles por alquiler de congeladora.'), 0, 1);

        $fpdf->Ln();

        $fpdf->Cell(190, 5, 'Por todo ello, firman a dia ' . $contract->date->format('d/m/Y'), 0, 1);

        $fpdf->Ln(40);

        $fpdf->Cell(70, 5, 'CLIENTE', 'T', 0, 'C');
        $fpdf->Cell(50, 5);
        $fpdf->Cell(70, 5, 'PRESTADOR DE SERVICIOS', 'T', 0, 'C');



        $filename = 'Contrato_' . $contract->id . '.pdf';

        $fpdf->Output('I', $filename);
    }

    public function pdf2(Contract $contract)
    {
        $fpdf = new Fpdf();

        $fpdf->AddPage();

        $fpdf->AddFont('Montserrat', '');
        $fpdf->AddFont('Montserrat', 'B');

        $fpdf->Image(asset('assets/images/logonew2.png'), 85, 15, 45);

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
