@extends('template.app')

@section('title', 'Contratos')

@section('content')
<nav class="mb-2">
  <ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="{{ url('/') }}">Inicio</a></li>
    <li class="breadcrumb-item"><a href="{{ route('contracts.index') }}">Contratos</a></li>
    <li class="breadcrumb-item active">Cobranzas</li>
  </ol>
</nav>

<div class="card">
	<div class="card-header d-flex justify-content-between flex-column flex-sm-row gap-2">
		<div>
			<a href="{{ route('export', ['module' => 'charges', 'format' => 'pdf']) }}" class="btn btn-outline-danger" target="_blank" data-bs-toggle="tooltip" title="Exportar a PDF">
				<i class="ti ti-file-type-pdf icon"></i> PDF
			</a>
			<a href="{{ route('export', ['module' => 'charges', 'format' => 'excel']) }}" class="btn btn-outline-success" data-bs-toggle="tooltip" title="Exportar a Excel">
				<i class="ti ti-file-spreadsheet icon"></i> Excel
			</a>
		</div>
		<div class="text-center">
			<span class="d-block small">
				Tienes un total de deuda de
			</span>
			<span class="fs-2 fw-bold text-primary">
					S/{{ number_format($total, 2) }}
				</span>
		</div>
	</div>
	<div class="card-body border-bottom">
		<form>
			<div class="row">
				<div class="col-lg-3">
					<div class="mb-3">
						<label class="form-label">Nombre o Código</label>
						<input type="text" class="form-control" name="name" value="{{ request()->name }}">
					</div>
				</div>
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
			<a href="{{ route('contracts.charges') }}" class="btn btn-danger">Limpiar</a>
		</form>
	</div>
	<div class="table-responsive">
		<table class="table card-table table-vcenter">
			<thead>
				<tr>
					<th>DNI</th>
					<th>Código</th>
					<th>Nombre</th>
					<th>Fecha de evento</th>
					<th>Paquete</th>
					<th>Total inicial</th>
					<th>Pago inicial</th>
					<th>Deuda</th>
					<th>Fecha de pago de deuda</th>
					<th>Acción</th>
				</tr>
			</thead>
			<tbody>
				@if($contracts->count() > 0)
				@foreach($contracts as $contract)
				<tr>
					<td>{{ $contract->document }}</td>
					<td>{{ $contract->code }}</td>
					<td>{{ $contract->name }}</td>
					<td>{{ $contract->event_date->format('d/m/Y') }}</td>
					<td>{{ optional($contract->package)->name }}</td>
					<td>S/{{ $contract->total }}</td>
					<td>S/{{ $contract->initial_payment }}</td>
					<td>S/{{ $contract->debt }}</td>
					<td>{{ $contract->debt_payment_date->format('d/m/Y') }}</td>
					<td>
						<div class="d-flex gap-2">
							<div class="d-flex gap-2">
								<button class="btn btn-icon btn-primary btn-payment" data-id="{{ $contract->id }}" title="Pagar" data-bs-toggle="tooltip">
									<i class="ti ti-cash icon"></i>
								</button>
								<button class="btn btn-icon btn-primary btn-payments" data-id="{{ $contract->id }}" title="Pagos realizados" data-bs-toggle="tooltip">
									<i class="ti ti-list icon"></i>
								</button>
							</div>
						</div>
					</td>		
				</tr>
				@endforeach
				@else
				<tr>
					<td colspan="12" align="center">No se han encontrado resultados</td>
				</tr>
				@endif
			</tbody>
		</table>
	</div>
	@if($contracts->hasPages())
	<div class="card-footer d-flex align-items-center">
		{{ $contracts->withQueryString()->links() }}
	</div>
	@endif
</div>

<div class="modal modal-blur fade" id="paymentModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
  	<div class="modal-content">
  		<form id="paymentForm" method="POST">
  			<div class="modal-header">
  			  <h5 class="modal-title">Pagar</h5>
  			  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
  			</div>
  			<div class="modal-body">
  				<div class="row">
  					<div class="col-lg-6">
  						<div class="mb-3">
  							<label class="form-label required">Número de operación</label>
  							<input type="text" class="form-control" name="operation_number">
  						</div>
  					</div>
  					<div class="col-lg-6">
  						<div class="mb-3">
  							<label class="form-label required">Monto</label>
  							<input type="text" class="form-control" name="amount">
  						</div>
  					</div>
  					<div class="col-lg-6">
  						<div class="mb-3">
  							<label class="form-label required">Método de pago</label>
  							<select class="form-select" name="payment_method_id">
  								<option value="">Seleccionar</option>
  								@foreach($payment_methods as $payment_method)
  								<option value="{{ $payment_method->id }}">{{ $payment_method->name }}</option>
  								@endforeach
  							</select>
  						</div>
  					</div>
  					<div class="col-lg-6">
  						<div class="mb-3">
  							<label class="form-label required">Fecha</label>
  							<input type="date" class="form-control" name="date" value="{{ now()->format('Y-m-d') }}">
  						</div>
  					</div>
  				</div>
  			</div>
  			<div class="modal-footer">
  				<input type="hidden" id="contractId">
  			  <button type="button" class="btn me-auto" data-bs-dismiss="modal"><i class="ti ti-x icon"></i> Cerrar</button>
  			  <button type="submit" class="btn btn-primary"><i class="ti ti-device-floppy icon"></i> Guardar</button>
  			</div>
  		</form>
    </div>
  </div>
</div>

<div class="modal modal-blur fade" id="paymentsModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
  	<div class="modal-content">
  		<div class="modal-header">
  		  <h5 class="modal-title">Pagos realizados</h5>
  		  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
  		</div>
  		<div class="modal-body">
  		  <table class="table">
  		  	<thead>
  		  		<tr>
  		  			<th>Número de operación</th>
  		  			<th>Monto</th>
  		  			<th>Método de pago</th>
  		  			<th>Fecha</th>
  		  		</tr>
  		  	</thead>
  		  	<tbody id="tbl-payments"></tbody>
  		  </table>
  		</div>
    </div>
  </div>
</div>
@endsection

@section('scripts')
<script>

	$(document).on('click', '.btn-payment', function(){

		var id = $(this).data('id');

		console.log(id);

		$('#contractId').val(id);
		$('#paymentModal').modal('show');

	});

	$('#paymentForm').submit(function(e){
		e.preventDefault();

		var id = $('#contractId').val();

		$.ajax({
			url: '{{ route('contracts.index') }}' + '/' + id + '/payment',
			method: 'POST',
			data: $(this).serialize(),
			success: function(data){
				if(data.status){
					$('#paymentModal').modal('hide');
					$('#paymentForm')[0].reset();
					ToastMessage.fire({ text: 'Registro guardado' })
						.then(() => location.reload());
				}else{
					ToastError.fire({ text: data.error ? data.error : 'Ocurrió un error' });
				}
			},
			error: function(err){
				ToastError.fire({ text: 'Ocurrió un error' });
			}
		});

	});

	$(document).on('click', '.btn-payments', function(){

		var id = $(this).data('id');

		$.ajax({
			url: '{{ route('contracts.index') }}' + '/' + id + '/payments',
			method: 'GET',
			success: function(data){
				if(data.status){
					var html = '';

					data.payments.forEach(function(payment){
						html += `
							<tr>
								<td>${payment.operation_number ?? '' }</td>
								<td>${payment.amount}</td>
								<td>${payment.payment_method ?? '' }</td>
								<td>${payment.date}</td>
							</tr>
						`;
					});

					$('#tbl-payments').html(html);

					$('#paymentsModal').modal('show');
				}
			},
			error: function(err){
				console.log(err);
			}
		});

	});




</script>
@endsection