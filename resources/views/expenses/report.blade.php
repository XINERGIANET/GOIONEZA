@extends('template.app')

@section('title', 'Egresos generales - Reporte')

@section('content')
<nav class="mb-2">
  <ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="{{ url('/') }}">Inicio</a></li>
    <li class="breadcrumb-item"><a href="{{ route('purchases.index') }}">Egresos generales</a></li>
    <li class="breadcrumb-item active">Reporte</li>
  </ol>
</nav>
<div class="card">
	<div class="card-body border-bottom">
		<form>
			<div class="row">
				<div class="col-lg-3">
					<div class="mb-3">
						<label class="form-label">Fecha inicial</label>
						<input type="date" class="form-control" name="start_date" value="{{ request()->start_date }}">
					</div>
				</div>
				<div class="col-lg-3">
					<div class="mb-3">
						<label class="form-label">Fecha final</label>
						<input type="date" class="form-control" name="end_date" value="{{ request()->end_date }}">
					</div>
				</div>
			</div>
			<button type="submit" class="btn btn-primary">
				Filtrar
			</button>
			<a href="{{ route('expenses.report') }}" class="btn btn-red">
				Limpiar
			</a>
		</form>
	</div>
	<div class="table-responsive">
		<table class="table card-table table-vcenter table-sm">
			<thead>
				<tr>
					<th>Descripción</th>
					<th>Monto</th>
					<th>Fecha</th>
				</tr>
			</thead>
			<tbody>
				@if($contracts->count() > 0)

					@foreach($contracts as $contract)

						<tr class="bg-blue-lt">
							<td colspan="3" class="fw-bold">Contrato: {{ optional($contract->contract)->name.' - '.optional(optional($contract->contract)->package)->name.' - '.optional($contract->contract)->date->format('d/m/Y') }}</td>
						</tr>
						
						@php
						$providers = App\Models\Expense::active()->select('provider')->when(request()->start_date, function($query, $start_date){
            	return $query->whereDate('date', '>=', $start_date);
        		})->when(request()->end_date, function($query, $end_date){
          		return $query->whereDate('date', '<=', $end_date);
        		})->where('contract_id', $contract->contract_id)->latest('date')->get();
						@endphp

						@foreach($providers as $provider)

							<tr>
								<td colspan="3" class="fw-bold">Proveedor: {{ $provider->provider }}</td>
							</tr>

							@php
							$expenses = App\Models\Expense::active()->when(request()->start_date, function($query, $start_date){
            		return $query->whereDate('date', '>=', $start_date);
        			})->when(request()->end_date, function($query, $end_date){
          			return $query->whereDate('date', '<=', $end_date);
        			})->where('contract_id', $contract->contract_id)->where('provider', $provider->provider)->latest('date')->get();
							@endphp

							@foreach($expenses as $expense)
							<tr>
								<td>{{ $expense->description }}</td>
								<td>{{ $expense->amount }}</td>
								<td>{{ $expense->date->format('d/m/Y') }}</td>
							</tr>
							@endforeach

						@endforeach

					@endforeach

				@else
				<tr>
					<td colspan="3" align="center">No se han encontrado resultados</td>
				</tr>
				@endif
			</tbody>
		</table>
	</div>
</div>
@endsection