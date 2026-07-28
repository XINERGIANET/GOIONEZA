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

            // Determinar alineaciones dinámicas según las columnas
            $aligns = $this->getAlignmentsFromHeadings($headings);

            // Dibujar encabezados con fuente Montserrat Bold 8pt y fondo gris claro
            try {
                $fpdf->SetFont('Montserrat', 'B', 8);
            } catch (\Exception $e) {
                $fpdf->SetFont('Arial', 'B', 8);
            }
            $fpdf->SetFillColor(240, 240, 240);
            $this->drawWrappedRow($fpdf, $widths, $headings, 4, 2, 'C', true, false);

            // Dibujar filas de datos con fuente Montserrat Regular 7.5pt
            try {
                $fpdf->SetFont('Montserrat', '', 7.5);
            } catch (\Exception $e) {
                $fpdf->SetFont('Arial', '', 7.5);
            }

            foreach ($data as $row) {
                $this->drawWrappedRow($fpdf, $widths, $row, 4, 2, $aligns, false, true, $headings);
            }

            $fpdf->Output('D', $module . '_' . date('Ymd_His') . '.pdf');
            exit;
        }

        return back();
    }

    /**
     * Divide un texto en múltiples líneas de un ancho máximo en FPDF.
     * Soporta caracteres codificados en ISO-8859-1 (conversiones utf8_decode).
     */
    private function splitTextIntoLines($fpdf, $text, $width)
    {
        $text = (string) $text; // Asegurar que sea string
        $lines = [];
        $availableWidth = $width - 2; // Margen de 1mm por lado
        if ($availableWidth <= 0) {
            $availableWidth = 1;
        }
        
        $text = str_replace("\r", "", $text);
        $textRows = explode("\n", $text);
        
        foreach ($textRows as $row) {
            $words = explode(' ', $row);
            $currentLine = '';
            foreach ($words as $word) {
                $testLine = $currentLine === '' ? $word : $currentLine . ' ' . $word;
                if ($fpdf->GetStringWidth($testLine) <= $availableWidth) {
                    $currentLine = $testLine;
                } else {
                    if ($currentLine !== '') {
                        $lines[] = $currentLine;
                        $currentLine = $word;
                    } else {
                        // Si la palabra es más ancha que la celda, la dividimos letra por letra
                        $chars = str_split($word);
                        $temp = '';
                        foreach ($chars as $char) {
                            if ($fpdf->GetStringWidth($temp . $char) <= $availableWidth) {
                                $temp .= $char;
                            } else {
                                if ($temp !== '') {
                                    $lines[] = $temp;
                                }
                                $temp = $char;
                            }
                        }
                        $currentLine = $temp;
                    }
                }
            }
            if ($currentLine !== '') {
                $lines[] = $currentLine;
            }
        }
        
        if (empty($lines)) {
            $lines[] = '';
        }
        
        return $lines;
    }

    /**
     * Dibuja una fila con ajuste dinámico de múltiples líneas (hasta 2 filas por celda).
     * Si el texto tiene 1 línea, lo centra verticalmente. Si tiene más de 2, lo trunca en la 2da.
     */
    private function drawWrappedRow($fpdf, $widths, $row, $lineHeight = 4, $maxLines = 2, $align = 'L', $fill = false, $isDataRow = true, $headings = null)
    {
        $rowLines = [];
        $actualMaxLines = 1;
        
        foreach ($row as $index => $cell) {
            $lines = $this->splitTextIntoLines($fpdf, $cell, $widths[$index]);
            $rowLines[$index] = $lines;
            if (count($lines) > $actualMaxLines) {
                $actualMaxLines = count($lines);
            }
        }
        
        if ($actualMaxLines > $maxLines) {
            $actualMaxLines = $maxLines;
        }
        
        $rowHeight = $actualMaxLines * $lineHeight;
        $startX = $fpdf->GetX();
        $startY = $fpdf->GetY();
        
        // Control de salto de página
        $pageHeight = method_exists($fpdf, 'GetPageHeight') ? $fpdf->GetPageHeight() : 297; // 297mm es el estándar A4
        if ($startY + $rowHeight > $pageHeight - 15) {
            $fpdf->AddPage();
            $fpdf->Ln(10); // Margen superior para el inicio de tabla en la nueva página
            
            // Si es una fila de datos, redibujar los encabezados en la nueva página
            if ($isDataRow && !empty($headings) && !empty($widths)) {
                try {
                    $fpdf->SetFont('Montserrat', 'B', 8);
                } catch (\Exception $e) {
                    $fpdf->SetFont('Arial', 'B', 8);
                }
                $fpdf->SetFillColor(240, 240, 240);
                $this->drawWrappedRow($fpdf, $widths, $headings, 4, 2, 'C', true, false);
                
                try {
                    $fpdf->SetFont('Montserrat', '', 7.5);
                } catch (\Exception $e) {
                    $fpdf->SetFont('Arial', '', 7.5);
                }
            }
            
            $startX = $fpdf->GetX();
            $startY = $fpdf->GetY();
        }
        
        foreach ($row as $index => $cell) {
            $w = $widths[$index];
            $lines = $rowLines[$index];
            $cellAlign = is_array($align) ? ($align[$index] ?? 'L') : $align;
            
            // Dibujar borde y fondo de la celda vacía
            $fpdf->Cell($w, $rowHeight, '', 1, 0, 'C', $fill);
            
            $nextX = $fpdf->GetX();
            $availableWidth = $w - 2;
            if ($availableWidth <= 0) {
                $availableWidth = 1;
            }
            
            // Escribir texto de la celda respetando la alineación y el centrado vertical
            if (count($lines) >= 2 && $actualMaxLines >= 2) {
                // Fila 1
                $fpdf->SetXY($startX + 1, $startY + ($rowHeight - (2 * $lineHeight)) / 2);
                $fpdf->Cell($w - 2, $lineHeight, $lines[0] ?? '', 0, 0, $cellAlign);
                
                // Fila 2
                $line2 = $lines[1] ?? '';
                if (count($lines) > 2) {
                    while ($fpdf->GetStringWidth($line2 . '..') > $availableWidth && strlen($line2) > 1) {
                        $line2 = substr($line2, 0, -1);
                    }
                    $line2 .= '..';
                }
                $fpdf->SetXY($startX + 1, $startY + ($rowHeight - (2 * $lineHeight)) / 2 + $lineHeight);
                $fpdf->Cell($w - 2, $lineHeight, $line2, 0, 0, $cellAlign);
            } else {
                // Única fila centrada verticalmente
                $fpdf->SetXY($startX + 1, $startY + ($rowHeight - $lineHeight) / 2);
                $fpdf->Cell($w - 2, $lineHeight, $lines[0] ?? '', 0, 0, $cellAlign);
            }
            
            $startX = $nextX;
            $fpdf->SetXY($startX, $startY);
        }
        
        $fpdf->Ln($rowHeight);
    }

    /**
     * Determina las alineaciones dinámicas de cada columna según el texto del encabezado.
     */
    private function getAlignmentsFromHeadings($headings)
    {
        $aligns = [];
        foreach ($headings as $heading) {
            $headingClean = str_replace(
                ['á', 'é', 'í', 'ó', 'ú', 'ñ'],
                ['a', 'e', 'i', 'o', 'u', 'n'],
                mb_strtolower(trim($heading))
            );
            
            if (strpos($headingClean, 'metodo') !== false) {
                $aligns[] = 'C';
            } elseif ($headingClean === 'monto' || $headingClean === 'total' || $headingClean === 'deuda' || $headingClean === 'dscto' || $headingClean === 'pago inicial') {
                $aligns[] = 'R';
            } elseif (
                $headingClean === 'fecha' || 
                $headingClean === 'dia' || 
                $headingClean === 'numero' || 
                $headingClean === 'num.' || 
                $headingClean === 'dni' || 
                $headingClean === 'codigo' || 
                $headingClean === 'dni/ruc' || 
                $headingClean === 'duracion' || 
                $headingClean === 'pax'
            ) {
                $aligns[] = 'C';
            } else {
                $aligns[] = 'L';
            }
        }
        return $aligns;
    }
}
