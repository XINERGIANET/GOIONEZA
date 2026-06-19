@extends('template.app')

@section('title', 'Indicadores de gestión')

@section('content')
@if(auth()->user()->role == 'admin')
<div class="mb-4">
	<div class="mb-4">
		<a class="btn btn-primary" href="{{ route('contracts.index', ['modal' => 'create']) }}">Crear nuevo contrato</a>
	</div>
	<form>
		<div class="row">
			<div class="col-md-3">
				<div class="mb-3">
					<label class="form-label">Fecha inicial</label>
					<input type="date" class="form-control" name="start_date" value="{{ request()->start_date }}">
				</div>
			</div>
			<div class="col-md-3">
				<div class="mb-3">
					<label class="form-label">Fecha inicial</label>
					<input type="date" class="form-control" name="end_date" value="{{ request()->end_date }}">
				</div>
			</div>
		</div>
		<button type="submit" class="btn btn-primary">Filtrar</button>
		<a href="{{ url('/') }}" class="btn btn-danger">Limpiar</a>
	</form>
</div>
<div class="row">
	<div class="col-md-3 mb-4">
		<div class="card">
			<div class="card-body">
				<h5 class="card-title">
					Ventas totales
				</h5>
				<div>
				  <button class="btn btn-primary mb-2" data-bs-toggle="collapse" data-bs-target="#collapse1">Mostrar</button>
				  <div class="collapse" id="collapse1">
				    <span class="fw-bold fs-1">S/{{ number_format($total_contracts + $total_payments + $total_incomes, 2) }}</span>
				  </div>
				</div>
			</div>
		</div>
	</div>
	<div class="col-md-3">
		<div class="card mb-4">
			<div class="card-body">
				<h5 class="card-title">
					Egresos totales
				</h5>
				<div>
					<button class="btn btn-primary mb-2" data-bs-toggle="collapse" data-bs-target="#collapse2">Mostrar</button>
					<div class="collapse" id="collapse2">
						<span class="fw-bold fs-1">S/{{ number_format($total_expenses + $total_purchases, 2) }}</span>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="col-md-3">
		<div class="card mb-4">
			<div class="card-body">
				<h5 class="card-title">
					Rentabilidad
				</h5>
				<div>
					<button class="btn btn-primary mb-2" data-bs-toggle="collapse" data-bs-target="#collapse3">Mostrar</button>
					<div class="collapse" id="collapse3">
						<span class="fw-bold fs-1">S/{{ number_format($total_contracts + $total_payments + $total_incomes - $total_expenses - $total_purchases, 2) }}</span>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="col-md-3">
		<div class="card mb-4">
			<div class="card-body">
				<h5 class="card-title">
					Cierre de caja
				</h5>
				<div>
					<button class="btn btn-primary mb-2" data-bs-toggle="collapse" data-bs-target="#collapse4">Mostrar</button>
					<a class="btn btn-primary mb-2" href="{{ route('cash_report', request()->all()) }}">PDF</a>
					<div class="collapse" id="collapse4">
						<ul class="fs-3 fw-bold mb-0">
							<li>Efectivo: S/{{ number_format($efectivo, 2) }}</li>
							<li>BCP: S/{{ number_format($bcp, 2) }}</li>
							<li>BBVA: S/{{ number_format($bbva, 2) }}</li>
							<li>SCK: S/{{ number_format($sck, 2) }}</li>
							<li>IBK: S/{{ number_format($ibk, 2) }}</li>
						</ul>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="col-md-3">
		<div class="card mb-4">
			<div class="card-body">
				<h5 class="card-title">
					Total de cuentas por cobrar
				</h5>
				<div>
					<button class="btn btn-primary mb-2" data-bs-toggle="collapse" data-bs-target="#collapse5">Mostrar</button>
					<div class="collapse" id="collapse5">
						<span class="fw-bold fs-1">S/{{ number_format($total_charges, 2) }}</span>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="col-md-9">
		<div class="card mb-4">
			<div class="card-body">
				<h5 class="card-title">
					Proximos 03 eventos
				</h5>
				<table class="table">
					<thead>
						<tr>
							<th>Tipo de evento</th>
							<th>Paquete</th>
							<th>Fecha</th>
							<th>Duración</th>
						</tr>
					</thead>
					<tbody>
						@foreach($events as $event)
						<tr>
							<td>{{ $event->event_type->name }}</td>
							<td>{{ $event->package->name }}</td>
							<td>{{ $event->event_date->format('d/m/Y') }} {{ $event->event_time->format('H:i') }} - {{ $event->event_end->format('H:i') }}</td>
							<td>{{ $event->event_duration }} horas</td>
						</tr>
						@endforeach
					</tbody>
				</table>
			</div>
		</div>
	</div>
</div>
@else
<p>Bienvenidos a Quinta Fernandini</p>
@endif
@endsection