@extends('template.app')

@section('title', 'Gastos por personal')

@section('content')
<nav class="mb-2">
  <ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="{{ url('/') }}">Inicio</a></li>
    <li class="breadcrumb-item active">Gastos por personal</li>
  </ol>
</nav>

<div class="card">
	<div class="card-header d-flex justify-content-between flex-column flex-sm-row gap-2">
		<div>
			<button class="btn btn-red" data-bs-toggle="modal" data-bs-target="#createModal">
				<i class="ti ti-plus icon"></i> Crear gasto personal
			</button>
		</div>
		<div>
			<span class="d-block small">
				Tienes un total de:
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
						<label class="form-label">Personal (Descripción)</label>
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
			<a href="{{ route('personnel_expenses.index') }}" class="btn btn-danger">Limpiar</a>
		</form>
	</div>
	<div class="table-responsive">
		<table class="table card-table table-vcenter">
			<thead>
				<tr>
					<th>Evento</th>
					<th>Descripción</th>
					<th>Personal</th>
					<th>Comprobante</th>
					<th>Monto</th>
					<th>Método de pago</th>
					<th>Fecha</th>
					<th>Acción</th>
				</tr>
			</thead>
			<tbody>
				@if($expenses->count() > 0)
				@foreach($expenses as $expense)
				<tr>
					<td>{{ optional($expense->contract)->name.' - '.optional(optional($expense->contract)->date)->format('d/m/Y') }}</td>
					<td>{{ $expense->description }}</td>
					<td>{{ $expense->provider }}</td>
					<td>{{ $expense->voucher }} {{ $expense->voucher_number }}</td>
					<td>S/ {{ $expense->amount }}</td>
					<td>{{ optional($expense->payment_method)->name }}</td>
					<td>{{ optional($expense->date)->format('d/m/Y') }}</td>
					<td>
						<div class="d-flex gap-2">
							<a href="{{ route('expenses.pdf', $expense->id) }}" target="_blank" class="btn btn-icon btn-primary" data-bs-toggle="tooltip" title="Imprimir PDF">
								<i class="ti ti-printer icon"></i>
							</a>
							<button class="btn btn-icon btn-red btn-delete" data-id="{{ $expense->id }}" data-bs-toggle="tooltip" title="Eliminar">
								<i class="ti ti-x icon"></i>
							</button>
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
	@if($expenses->hasPages())
	<div class="card-footer d-flex align-items-center">
		{{ $expenses->withQueryString()->links() }}
	</div>
	@endif
</div>

<div class="modal modal-blur fade" id="createModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-xl" role="document">
  	<div class="modal-content">
		<div class="modal-header">
			<h5 class="modal-title">Crear gasto personal</h5>
			<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
		</div>
		<div class="modal-body">
			<div class="row w-100 mb-3">
				<div class="col-lg-6">
					<label class="form-label required">Seleccionar Evento</label>
					<select class="form-select" id="selectContract">
						<option value="">Seleccionar</option>
						@foreach($contracts as $contract)
						<option value="{{ $contract->id }}">{{ $contract->name.' - '.optional($contract->package)->name.' - '.optional($contract->event_date)->format('d/m/Y') }}</option>
						@endforeach
					</select>
				</div>
			</div>
			
			<div class="table-responsive">
				<table class="table card-table table-vcenter">
					<thead>
						<tr>
							<th>Personal</th>
							<th>Cargo / Función</th>
							<th>Monto a pagar</th>
							<th>Método de pago</th>
							<th>Comprobante</th>
							<th>N° Comp.</th>
							<th>Acción</th>
						</tr>
					</thead>
					<tbody id="personnelTableBody">
						<tr>
							<td colspan="7" align="center" class="text-muted">Seleccione un evento para ver el personal asignado</td>
						</tr>
					</tbody>
				</table>
			</div>
		</div>
		<div class="modal-footer">
			<button type="button" class="btn me-auto" data-bs-dismiss="modal"><i class="ti ti-x icon"></i> Cerrar</button>
		</div>
    </div>
  </div>
</div>
@endsection

@section('scripts')
<script>
	let hasChanges = false;

	$('#selectContract').change(function() {
		var contractId = $(this).val();
		if (!contractId) {
			$('#personnelTableBody').html('<tr><td colspan="7" align="center" class="text-muted">Seleccione un evento para ver el personal asignado</td></tr>');
			return;
		}

		$('#personnelTableBody').html('<tr><td colspan="7" align="center">Cargando...</td></tr>');

		$.ajax({
			url: '/contracts/' + contractId + '/schedules',
			method: 'GET',
			success: function(data) {
				var html = '';
				if(data.employees && data.employees.length > 0) {
					data.employees.forEach(function(emp, index) {
						if (emp.is_paid) {
							html += `
							<tr>
								<td>${emp.name}</td>
								<td>${emp.job} / ${emp.function}</td>
								<td>
									<input type="number" step="0.01" class="form-control" disabled value="${emp.event_payment || ''}">
								</td>
								<td>
									<select class="form-select" disabled>
										<option>Pagado</option>
									</select>
								</td>
								<td>
									<select class="form-select" disabled>
										<option>-</option>
									</select>
								</td>
								<td>
									<input type="text" class="form-control" disabled value="-">
								</td>
								<td>
									<button class="btn btn-success" disabled>
										<i class="ti ti-check icon"></i> Pagado
									</button>
								</td>
							</tr>
							`;
						} else {
							html += `
							<tr>
								<td>${emp.name}</td>
								<td>${emp.job} / ${emp.function}</td>
								<td>
									<input type="number" step="0.01" class="form-control emp-amount" id="amount_${index}" placeholder="Ej. 120.00" value="${emp.event_payment || ''}">
								</td>
								<td>
									<select class="form-select emp-method" id="method_${index}">
										<option value="">Seleccionar</option>
										@foreach($payment_methods as $pm)
										<option value="{{ $pm->id }}">{{ $pm->name }}</option>
										@endforeach
									</select>
								</td>
								<td>
									<select class="form-select emp-voucher" id="voucher_${index}">
										<option value="Recibo">Recibo</option>
										<option value="Boleta">Boleta</option>
										<option value="Factura">Factura</option>
										<option value="Otro">Otro</option>
									</select>
								</td>
								<td>
									<input type="text" class="form-control emp-voucher-number" id="voucher_num_${index}" value="-" placeholder="N°">
								</td>
								<td>
									<button class="btn btn-primary btn-pay-emp" 
										data-name="${emp.name}" 
										data-index="${index}" 
										data-contract="${contractId}">
										<i class="ti ti-cash icon"></i> Pagar
									</button>
								</td>
							</tr>
							`;
						}
					});
				} else {
					html = '<tr><td colspan="7" align="center">No hay personal asignado a este evento</td></tr>';
				}
				$('#personnelTableBody').html(html);
			},
			error: function() {
				$('#personnelTableBody').html('<tr><td colspan="7" align="center" class="text-danger">Error al cargar personal</td></tr>');
			}
		});
	});

	$(document).on('click', '.btn-pay-emp', function() {
		var btn = $(this);
		var index = btn.data('index');
		var name = btn.data('name');
		var contractId = btn.data('contract');

		var amount = $('#amount_' + index).val();
		var method = $('#method_' + index).val();
		var voucher = $('#voucher_' + index).val();
		var voucherNum = $('#voucher_num_' + index).val();

		if (!amount || !method || !voucher || !voucherNum) {
			ToastError.fire({ text: 'Por favor complete todos los campos de pago' });
			return;
		}

		btn.prop('disabled', true).html('<i class="spinner-border spinner-border-sm"></i>');

		$.ajax({
			url: '{{ route('expenses.store') }}',
			method: 'POST',
			data: {
				contract_id: contractId,
				description: 'Pago a personal: ' + name,
				responsible: '{{ auth()->user()->name ?? 'Sistema' }}',
				voucher: voucher,
				voucher_number: voucherNum,
				provider: name,
				amount: amount,
				payment_method_id: method,
				date: '{{ now()->format('Y-m-d') }}'
			},
			success: function(data) {
				if(data.status){
					ToastMessage.fire({ text: 'Pago registrado correctamente' });
					btn.removeClass('btn-primary').addClass('btn-success').html('<i class="ti ti-check icon"></i> Pagado').prop('disabled', true);
					hasChanges = true;
				} else {
					ToastError.fire({ text: data.error ? data.error : 'Error al registrar' });
					btn.prop('disabled', false).html('<i class="ti ti-cash icon"></i> Pagar');
				}
			},
			error: function() {
				ToastError.fire({ text: 'Ocurrió un error de red' });
				btn.prop('disabled', false).html('<i class="ti ti-cash icon"></i> Pagar');
			}
		});
	});

	$('#createModal').on('hidden.bs.modal', function () {
		if(hasChanges) {
			location.reload();
		}
	});

	$(document).on('click', '.btn-delete', function(){
		var id = $(this).data('id');
		ToastConfirm.fire({
			text: '¿Estás seguro que deseas eliminar el registro?',
		}).then((result) => {
			if(result.isConfirmed){
				$.ajax({
					url: '/expenses/' + id,
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
