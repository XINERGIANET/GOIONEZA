<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Codedge\Fpdf\Fpdf\Fpdf;
use App\Models\Client;
use App\Models\Provider;
use App\Models\Employee;

class ExportController extends Controller
{
    public function export($module, $format)
    {
        $data = [];
        $headings = [];
        $title = '';
        $widths = [];

        if ($module == 'clients') {
            $records = Client::active()->orderBy('name', 'asc')->get();
            $headings = ['DNI/RUC', 'Nombre', 'Telefono', 'Correo electronico'];
            $title = 'REPORTE DE CLIENTES';
            $widths = [40, 60, 40, 50];
            foreach ($records as $r) {
                $data[] = [$r->document, utf8_decode($r->name), $r->phone, $r->email];
            }
        } elseif ($module == 'providers') {
            $records = Provider::active()->orderBy('name', 'asc')->get();
            $headings = ['DNI/RUC', 'Nombre', 'Telefono', 'Correo electronico', 'Servicio'];
            $title = 'REPORTE DE PROVEEDORES';
            $widths = [30, 50, 30, 40, 40];
            foreach ($records as $r) {
                $data[] = [$r->document, utf8_decode($r->name), $r->phone, $r->email, utf8_decode($r->service)];
            }
        } elseif ($module == 'employees') {
            $records = Employee::active()->orderBy('name', 'asc')->get();
            $headings = ['DNI', 'Nombre', 'Puesto', 'Funcion'];
            $title = 'REPORTE DE PERSONAL';
            $widths = [35, 65, 45, 45];
            foreach ($records as $r) {
                $data[] = [$r->document, utf8_decode($r->name), utf8_decode($r->job), utf8_decode($r->function)];
            }
        } elseif ($module == 'locations') {
            $records = \App\Models\Location::active()->orderBy('name', 'asc')->get();
            $headings = ['Nombre'];
            $title = 'REPORTE DE LOCACIONES';
            $widths = [190];
            foreach ($records as $r) {
                $data[] = [utf8_decode($r->name)];
            }
        } elseif ($module == 'event_types') {
            $records = \App\Models\EventType::active()->orderBy('name', 'asc')->get();
            $headings = ['Nombre'];
            $title = 'REPORTE DE TIPOS DE EVENTO';
            $widths = [190];
            foreach ($records as $r) {
                $data[] = [utf8_decode($r->name)];
            }
        } elseif ($module == 'packages') {
            $records = \App\Models\Package::active()->orderBy('name', 'asc')->get();
            $headings = ['Nombre', 'Precio'];
            $title = 'REPORTE DE PAQUETES';
            $widths = [140, 50];
            foreach ($records as $r) {
                $data[] = [utf8_decode($r->name), $r->price];
            }
        } elseif ($module == 'extras') {
            $records = \App\Models\Extra::active()->orderBy('name', 'asc')->get();
            $headings = ['Nombre', 'Precio'];
            $title = 'REPORTE DE EXTRAS';
            $widths = [140, 50];
            foreach ($records as $r) {
                $data[] = [utf8_decode($r->name), $r->price];
            }
        } elseif ($module == 'income_types') {
            $records = \App\Models\IncomeType::active()->orderBy('name', 'asc')->get();
            $headings = ['Nombre'];
            $title = 'CATEGORIAS DE INGRESO';
            $widths = [190];
            foreach ($records as $r) {
                $data[] = [utf8_decode($r->name)];
            }
        } elseif ($module == 'expense_types') {
            $records = \App\Models\ExpenseType::active()->orderBy('name', 'asc')->get();
            $headings = ['Nombre'];
            $title = 'CATEGORIAS DE EGRESO';
            $widths = [190];
            foreach ($records as $r) {
                $data[] = [utf8_decode($r->name)];
            }
        } elseif ($module == 'product_types') {
            $records = \App\Models\ProductType::active()->orderBy('name', 'asc')->get();
            $headings = ['Nombre'];
            $title = 'CATEGORIAS DE ALMACEN';
            $widths = [190];
            foreach ($records as $r) {
                $data[] = [utf8_decode($r->name)];
            }
        } elseif ($module == 'products') {
            $records = \App\Models\Product::with(['product_type', 'location_model', 'sublocation'])->orderBy('name', 'asc')->get();
            $headings = ['Codigo', 'Nombre', 'Tipo', 'Ubicacion', 'Lado', 'Stock'];
            $title = 'REPORTE DE ALMACEN';
            $widths = [25, 50, 35, 30, 30, 20];
            foreach ($records as $r) {
                $loc = $r->location_model ? $r->location_model->name : $r->location;
                $subloc = $r->sublocation ? $r->sublocation->name : '-';
                $data[] = [$r->code, utf8_decode($r->name), utf8_decode(optional($r->product_type)->name), utf8_decode($loc), utf8_decode($subloc), $r->stock];
            }
        } elseif ($module == 'quotations') {
            $records = \App\Models\Quotation::with(['package'])->orderBy('id', 'desc')->get();
            $headings = ['Nombre', 'Telefono', 'Paquete', 'Pax', 'F. evento', 'F. rpta', 'F. crea'];
            $title = 'REPORTE DE COTIZACIONES';
            $widths = [45, 20, 30, 15, 25, 25, 30];
            foreach ($records as $r) {
                $data[] = [utf8_decode($r->name), $r->phone, utf8_decode(optional($r->package)->name), $r->people_number, $r->event_date ? $r->event_date->format('d/m/Y') : '', $r->answer_date ? $r->answer_date->format('d/m/Y') : '', $r->date ? $r->date->format('d/m/Y H:i') : ''];
            }
        } elseif ($module == 'contracts') {
            $records = \App\Models\Contract::with(['event_type', 'package'])->active()->orderBy('id', 'desc')->get();
            $headings = ['DNI', 'Codigo', 'Nombre', 'Tipo evento', 'Fecha evento', 'Duracion', 'Paquete', 'Pax', 'Dscto', 'Total'];
            $title = 'REPORTE DE CONTRATOS';
            $widths = [18, 15, 30, 25, 32, 15, 20, 10, 15, 10];
            foreach ($records as $r) {
                $dateString = $r->event_date ? $r->event_date->format('d/m/Y') : '';
                if ($r->event_time && $r->event_end) {
                    $dateString .= ' ' . $r->event_time->format('H:i') . '-' . $r->event_end->format('H:i');
                }
                $data[] = [$r->document, utf8_decode($r->code), utf8_decode($r->name), utf8_decode(optional($r->event_type)->name), $dateString, $r->event_duration . ' hrs', utf8_decode(optional($r->package)->name), $r->people_number, $r->discount, $r->total];
            }
        } elseif ($module == 'purchases') {
            $records = \App\Models\Purchase::with(['expense_type', 'payment_method'])->orderBy('date', 'desc')->get();
            $headings = ['Descripcion', 'Comprobante', 'Numero', 'Proveedor', 'Monto', 'Tipo egreso', 'Metodo pago', 'Fecha'];
            $title = 'EGRESOS GENERALES';
            $widths = [35, 25, 15, 35, 15, 25, 25, 15];
            foreach ($records as $r) {
                $data[] = [utf8_decode($r->description), utf8_decode($r->voucher), $r->voucher_number, utf8_decode($r->provider), $r->amount, utf8_decode(optional($r->expense_type)->name), utf8_decode(optional($r->payment_method)->name), $r->date ? $r->date->format('d/m/Y') : ''];
            }
        } elseif ($module == 'expenses') {
            $records = \App\Models\Expense::with(['contract.package', 'payment_method'])->orderBy('date', 'desc')->get();
            $headings = ['Evento', 'Descripcion', 'Resp.', 'Comprobante', 'Num.', 'Proveedor', 'Monto', 'Metodo pago', 'Fecha'];
            $title = 'GASTOS POR EVENTO';
            $widths = [30, 30, 20, 25, 15, 25, 15, 15, 15];
            foreach ($records as $r) {
                $eventName = $r->contract ? ($r->contract->name.' - '.optional($r->contract->package)->name) : '';
                $data[] = [utf8_decode($eventName), utf8_decode($r->description), utf8_decode($r->responsible), utf8_decode($r->voucher), $r->voucher_number, utf8_decode($r->provider), $r->amount, utf8_decode(optional($r->payment_method)->name), $r->date ? $r->date->format('d/m/Y') : ''];
            }
        } elseif ($module == 'incomes') {
            $records = \App\Models\Income::with(['income_type', 'payment_method', 'location'])->orderBy('date', 'desc')->get();
            $headings = ['Descripcion', 'Monto', 'Tipo ingreso', 'Metodo pago', 'Locacion', 'Fecha'];
            $title = 'OTROS INGRESOS';
            $widths = [50, 20, 30, 35, 35, 20];
            foreach ($records as $r) {
                $data[] = [utf8_decode($r->description), $r->amount, utf8_decode(optional($r->income_type)->name), utf8_decode(optional($r->payment_method)->name), utf8_decode(optional($r->location)->name), $r->date ? $r->date->format('d/m/Y') : ''];
            }
        } elseif ($module == 'charges') {
            $records = \App\Models\Contract::with(['package'])->where('debt', '>', 0)->orderBy('debt_payment_date', 'asc')->get();
            $headings = ['DNI', 'Codigo', 'Nombre', 'Fecha evento', 'Paquete', 'Total', 'Pago inicial', 'Deuda', 'Fecha pago'];
            $title = 'CUENTAS POR COBRAR';
            $widths = [20, 20, 40, 25, 25, 15, 15, 15, 15];
            foreach ($records as $r) {
                $data[] = [$r->document, utf8_decode($r->code), utf8_decode($r->name), $r->event_date ? $r->event_date->format('d/m/Y') : '', utf8_decode(optional($r->package)->name), $r->total, $r->initial_payment, $r->debt, $r->debt_payment_date ? $r->debt_payment_date->format('d/m/Y') : ''];
            }
        } elseif ($module == 'payment_schedules') {
            $records = \App\Models\PaymentSchedule::orderBy('day', 'asc')->get();
            $headings = ['Descripcion', 'Monto', 'Dia'];
            $title = 'CRONOGRAMA DE PAGOS';
            $widths = [110, 40, 40];
            foreach ($records as $r) {
                $data[] = [utf8_decode($r->description), $r->amount, $r->day];
            }
        } else {
            return back()->with('error', 'Módulo no soportado para exportación');
        }
        
        if ($format == 'excel') {
            $filename = $module . '_' . date('Ymd_His') . '.csv';
            $headers = [
                'Content-Type' => 'text/csv; charset=UTF-8',
                'Content-Disposition' => 'attachment; filename="' . $filename . '"',
            ];
            
            $callback = function() use ($headings, $data) {
                $file = fopen('php://output', 'w');
                // BOM to fix UTF-8 characters in Excel natively
                fputs($file, "\xEF\xBB\xBF");
                // Original CSV header mappings
                $decoded_headings = array_map('utf8_encode', $headings); 
                fputcsv($file, $decoded_headings, ';');
                foreach ($data as $row) {
                    $decoded_row = array_map('utf8_encode', $row);
                    fputcsv($file, $decoded_row, ';');
                }
                fclose($file);
            };

            return response()->stream($callback, 200, $headers);

        } elseif ($format == 'pdf') {
            $fpdf = new Fpdf();
            $fpdf->AddPage();
            
            try {
                $fpdf->AddFont('Montserrat', '');
                $fpdf->AddFont('Montserrat', 'B');
                $fpdf->SetFont('Montserrat', 'B', 14);
            } catch (\Exception $e) {
                $fpdf->SetFont('Arial', 'B', 14);
            }

            if(file_exists(public_path('assets/images/logonew2.png'))){
                $fpdf->Image(public_path('assets/images/logonew2.png'), 15, 15, 45);
            }
            $fpdf->Ln(20);

            $fpdf->Cell(190, 10, utf8_decode($title), 0, 1, 'C');
            $fpdf->Ln(5);

            try {
                $fpdf->SetFont('Montserrat', 'B', 10);
            } catch (\Exception $e) {
                $fpdf->SetFont('Arial', 'B', 10);
            }

            foreach ($headings as $index => $heading) {
                $fpdf->Cell($widths[$index], 10, utf8_decode($heading), 1, 0, 'C');
            }
            $fpdf->Ln();

            try {
                $fpdf->SetFont('Montserrat', '', 9);
            } catch (\Exception $e) {
                $fpdf->SetFont('Arial', '', 9);
            }

            foreach ($data as $row) {
                foreach ($row as $index => $cell) {
                    $fpdf->Cell($widths[$index], 8, substr($cell, 0, 30), 1, 0, 'L');
                }
                $fpdf->Ln();
            }

            $fpdf->Output('D', $module . '_' . date('Ymd_His') . '.pdf');
            exit;
        }

        return back();
    }
}
