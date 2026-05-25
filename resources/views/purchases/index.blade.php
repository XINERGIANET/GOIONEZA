@extends('template.app')

@section('title', 'Egresos generales')

@section('content')
<nav class="mb-2">
  <ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="{{ url('/') }}">Inicio</a></li>
    <li class="breadcrumb-item active">Egresos generales</li>
  </ol>
</nav>
<div class="card">
	<div class="card-header d-flex justify-content-between flex-column flex-sm-row gap-2">
		<div>
			<button class="btn btn-red" data-bs-toggle="modal" data-bs-target="#createModal">
				<i class="ti ti-plus icon"></i> Crear nuevo
			</button>
			<a href="{{ route('purchases.report') }}" class="btn btn-primary"><i class="ti ti-printer icon"></i> Reporte por proveedor</a>
		</div>
		<div class="text-center">
			<span class="d-block small">
				Tienes un total de
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
						<label class="form-label">Descripción</label>
						<input type="text" class="form-control" name="description" value="{{ request()->description }}">
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
			<a href="{{ route('purchases.index') }}" class="btn btn-danger">Limpiar</a>
		</form>
	</div>
	<div class="table-responsive">
		<table class="table card-table table-vcenter">
			<thead>
				<tr>
					<th>Descripción</th>
					<th>Comprobante</th>
					<th>Número</th>
					<th>Proveedor</th>
					<th>Monto</th>
					<th>Tipo de egreso</th>
					<th>Método de pago</th>
					<th>Fecha</th>
					<th>Acción</th>
				</tr>
			</thead>
			<tbody>
				@if($purchases->count() > 0)
					@foreach($purchases as $purchase)
					<tr>
						<td>{{ $purchase->description }}</td>
						<td>{{ $purchase->voucher }}</td>
						<td>{{ $purchase->voucher_number }}</td>
						<td>{{ $purchase->provider }}</td>
						<td>S/{{ $purchase->amount }}</td>
						<td>{{ optional($purchase->expense_type)->name }}</td>
						<td>{{ optional($purchase->payment_method)->name }}</td>
						<td>{{ $purchase->date->format('d/m/Y') }}</td>
						<td>
							<div class="d-flex gap-2">
								<div class="d-flex gap-2">
									<button class="btn btn-icon btn-primary btn-edit" data-id="{{ $purchase->id }}">
										<i class="ti ti-pencil icon"></i>
									</button>
									<button class="btn btn-icon btn-red btn-delete" data-id="{{ $purchase->id }}">
										<i class="ti ti-x icon"></i>
									</button>
								</div>
							</div>
						</td>		
					</tr>
					@endforeach
				@else
				<tr>
					<td colspan="8" align="center">No se han encontrado resultados</td>
				</tr>
				@endif
			</tbody>
		</table>
	</div>
	@if($purchases->hasPages())
	<div class="card-footer d-flex align-items-center">
		{{ $purchases->withQueryString()->links() }}
	</div>
	@endif
</div>
<div class="modal modal-blur fade" id="createModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
  	<div class="modal-content">
  		<form id="storeForm" method="POST">
  			<div class="modal-header">
  			  <h5 class="modal-title">Crear nuevo</h5>
  			  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
  			</div>
  			<div class="modal-body">
  			  <div class="row">
  			  	<div class="col-lg-6">
  			  		<div class="mb-3">
  			  			<label class="form-label required">Descripción</label>
  			  			<input type="text" class="form-control" name="description">
  			  		</div>
  			  	</div>
  			  	<div class="col-lg-6">
  			  		<div class="mb-3">
  			  			<label class="form-label required">Comprobante</label>
  			  			<select class="form-select" name="voucher">
  			  				<option value="">Seleccionar</option>
  			  				<option value="Boleta">Boleta</option>
  			  				<option value="Factura">Factura</option>
  			  				<option value="Otro">Otro</option>
  			  			</select>
  			  		</div>
  			  	</div>
  			  	<div class="col-lg-6">
  			  		<div class="mb-3">
  			  			<label class="form-label required">Número de comprobante</label>
  			  			<input type="text" class="form-control" name="voucher_number" autocomplete="off">
  			  		</div>
  			  	</div>
  			  	<div class="col-lg-6">
  			  		<div class="mb-3">
  			  			<label class="form-label required">Proveedor</label>
  			  			<input type="text" class="form-control" name="provider" autocomplete="off">
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
  			  			<label class="form-label required">Tipo de egreso</label>
  			  			<select class="form-select" name="expense_type_id">
  			  				<option value="">Seleccionar</option>
  			  				@foreach($expense_types as $expense_type)
  			  				<option value="{{ $expense_type->id }}">{{ $expense_type->name }}</option>
  			  				@endforeach
  			  			</select>
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
  			  <button type="button" class="btn me-auto" data-bs-dismiss="modal">Cerrar</button>
  			  <button type="submit" class="btn btn-primary">Guardar</button>
  			</div>
  		</form>
    </div>
  </div>
</div>

<div class="modal modal-blur fade" id="editModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
  	<div class="modal-content">
  		<form id="editForm" method="POST">
  			<div class="modal-header">
  			  <h5 class="modal-title">Editar</h5>
  			  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
  			</div>
  			<div class="modal-body">
  			  <div class="row">  			  	
  			  	<div class="col-lg-6">
  			  		<div class="mb-3">
  			  			<label class="form-label required">Descripción</label>
  			  			<input type="text" class="form-control" name="description" id="editDescription">
  			  		</div>
  			  	</div>
  			  	<div class="col-lg-6">
  			  		<div class="mb-3">
  			  			<label class="form-label required">Comprobante</label>
  			  			<select class="form-select" name="voucher" id="editVoucher">
  			  				<option value="">Seleccionar</option>
  			  				<option value="Boleta">Boleta</option>
  			  				<option value="Factura">Factura</option>
  			  				<option value="Otro">Otro</option>
  			  			</select>
  			  		</div>
  			  	</div>
  			  	<div class="col-lg-6">
  			  		<div class="mb-3">
  			  			<label class="form-label required">Número de comprobante</label>
  			  			<input type="text" class="form-control" name="voucher_number" id="editVoucherNumber" autocomplete="off">
  			  		</div>
  			  	</div>
  			  	<div class="col-lg-6">
  			  		<div class="mb-3">
  			  			<label class="form-label required">Proveedor</label>
  			  			<input type="text" class="form-control" name="provider" id="editProvider" autocomplete="off">
  			  		</div>
  			  	</div>
  			  	<div class="col-lg-6">
  			  		<div class="mb-3">
  			  			<label class="form-label required">Monto</label>
  			  			<input type="text" class="form-control" name="amount" id="editAmount">
  			  		</div>
  			  	</div>
  			  	<div class="col-lg-6">
  			  		<div class="mb-3">
  			  			<label class="form-label required">Tipo de egreso</label>
  			  			<select class="form-select" name="expense_type_id" id="editExpenseTypeId">
  			  				<option value="">Seleccionar</option>
  			  				@foreach($expense_types as $expense_type)
  			  				<option value="{{ $expense_type->id }}">{{ $expense_type->name }}</option>
  			  				@endforeach
  			  			</select>
  			  		</div>
  			  	</div>
  			  	<div class="col-lg-6">
  			  		<div class="mb-3">
  			  			<label class="form-label required">Método de pago</label>
  			  			<select class="form-select" name="payment_method_id" id="editPaymentMethodId">
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
  			  			<input type="date" class="form-control" name="date" id="editDate">
  			  		</div>
  			  	</div>
  			  </div>
  			</div>
  			<div class="modal-footer">
  				<input type="hidden" id="editId">
  			  <button type="button" class="btn me-auto" data-bs-dismiss="modal">Cerrar</button>
  			  <button type="submit" class="btn btn-primary">Guardar</button>
  			</div>
  		</form>
    </div>
  </div>
</div>
@endsection

@section('scripts')
<script>

	$('#storeForm').submit(function(e){
		e.preventDefault();

		$.ajax({
			url: '{{ route('purchases.store') }}',
			method: 'POST',
			data: $(this).serialize(),
			success: function(data){
				if(data.status){
					$('#createModal').modal('hide');
					$('#storeForm')[0].reset();
					
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

	$(document).on('click', '.btn-edit', function(){

		var id = $(this).data('id');

		$.ajax({
			url: '{{ route('purchases.index') }}' + '/' + id + '/edit/',
			method: 'GET',
			success: function(data){
				$('#editDescription').val(data.description);
				$('#editVoucher').val(data.voucher);
				$('#editVoucherNumber').val(data.voucher_number);
				$('#editProvider').val(data.provider);
				$('#editAmount').val(data.amount);
				$('#editExpenseTypeId').val(data.expense_type_id);
				$('#editPaymentMethodId').val(data.payment_method_id);
				$('#editDate').val(data.date);
				$('#editId').val(data.id);
				$('#editModal').modal('show');
			},
			error: function(err){
				ToastError.fire({ text: 'Ocurrió un error' });
			}
		});

	});

	$('#editForm').submit(function(e){
		e.preventDefault();

		var id = $('#editId').val();

		$.ajax({
			url: '{{ route('purchases.index') }}' + '/' + id + '',
			method: 'PATCH',
			data: $(this).serialize(),
			success: function(data){
				if(data.status){
					$('#editModal').modal('hide');
					$('#editForm')[0].reset();
					
					ToastMessage.fire({ text: 'Registro actualizado' })
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

	$(document).on('click', '.btn-delete', function(){

		var id = $(this).data('id');

		ToastConfirm.fire({
			text: '¿Estás seguro que deseas eliminar el registro?',
		}).then((result) => {
			
			if(result.isConfirmed){
				$.ajax({
					url: '{{ route('purchases.index') }}' + '/' + id,
					method: 'DELETE',
					success: function(data){
						ToastMessage.fire({ text: 'Registro eliminado' })
							.then(() => location.reload());
					},
					error: function(err){
						ToastError.fire({ text: 'Ocurrió un error' });
					}
				});
			}

		});

			

		

	});




</script>
@endsection