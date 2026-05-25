@extends('template.app')

@section('title', 'Flujo de caja')

@section('content')
<nav class="mb-2">
	<ol class="breadcrumb">
		<li class="breadcrumb-item"><a href="{{ url('/') }}">Inicio</a></li>
		<li class="breadcrumb-item active">Flujo de caja</li>
	</ol>
</nav>
<div class="card">
	<div class="card-body border-bottom">
		<form action="">
			<div class="row">
				<div class="col-md-3">
					<div class="mb-3">
						<label class="form-label">Año</label>
						<select class="form-select" name="year">
							<option value="">Seleccionar</option>
							@for($i = date('Y'); $i >= 2023; $i--)
							<option value="{{ $i }}" @if($year == $i) selected @endif>{{ $i }}</option>
							@endfor
						</select>
					</div>
				</div>
			</div>
			<button type="submit" class="btn btn-primary">Filtrar</button>
		</form>
	</div>
	<div class="table-responsive">
		<table class="table card-table table-vcenter table-bordered">
			<thead>
				<tr>
					<th>Flujo</th>
					<th>Ene</th>
					<th>Feb</th>
					<th>Mar</th>
					<th>Abr</th>
					<th>May</th>
					<th>Jun</th>
					<th>Jul</th>
					<th>Ago</th>
					<th>Set</th>
					<th>Oct</th>
					<th>Nov</th>
					<th>Dic</th>
				</tr>
			</thead>
			<tbody>
				<td colspan="13" class="fw-bold">Ingresos</td>
				<tr>
					<td>Contratos</td>
					@for($i = 1; $i <= 12; $i++)

					@php
					$payments = App\Models\Payment::whereYear('date', $year)->whereMonth('date', $i)->sum('amount');
					$totals['incomes'][$i] = floatval($payments);
					@endphp

					<td class="@if($payments > 0) bg-success-lt @elseif($payments < 0) bg-danger-lt @endif">
						S/{{ number_format($payments, 2)  }}
					</td>
					@endfor
				</tr>
				<tr>
					<td>Otros ingresos</td>
					@for($i = 1; $i <= 12; $i++)

					@php
					$incomes = App\Models\Income::active()->whereYear('date', $year)->whereMonth('date', $i)->sum('amount');
					$totals['incomes'][$i] += floatval($incomes);
					@endphp

					<td class="@if($incomes > 0) bg-success-lt @elseif($incomes < 0) bg-danger-lt @endif">
						S/{{ number_format($incomes, 2)  }}
					</td>
					@endfor
				</tr>
				<tr>
					<td colspan="13" class="fw-bold">Egresos</td>
				</tr>

				<tr>
					<td>Egresos generales</td>

					@for($i = 1; $i <= 12; $i++)

					@php
					$purchases = App\Models\Purchase::active()->whereYear('date', $year)->whereMonth('date', $i)->sum('amount');
					$totals['expenses'][$i] += floatval($purchases);
					@endphp

					<td class="@if($purchases > 0) bg-success-lt @elseif($purchases < 0) bg-danger-lt @endif">
						S/{{ number_format($purchases, 2)}}
					</td>

					@endfor
				</tr>

				<tr>
					<td>Gastos por evento</td>

					@for($i = 1; $i <= 12; $i++)

					@php
					$expenses = App\Models\Expense::active()->whereYear('date', $year)->whereMonth('date', $i)->sum('amount');
					$totals['expenses'][$i] += floatval($expenses);
					@endphp

					<td class="@if($expenses > 0) bg-success-lt @elseif($expenses < 0) bg-danger-lt @endif">
						S/{{ number_format($expenses, 2)}}
					</td>

					@endfor
				</tr>

				


				<tr>
					<td class="fw-bold">Rentabilidad bruta</td>
					@for($i = 1; $i <= 12; $i++)

					@php
					$profit = $totals['incomes'][$i] - $totals['expenses'][$i];
					@endphp

					<td class="@if($profit > 0) bg-success-lt @elseif($profit < 0) bg-danger-lt @endif">
						S/{{ number_format($profit, 2) }}
					</td>
					@endfor
				</tr>
			</tbody>
		</table>
	</div>
</div>

@endsection