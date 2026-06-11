@extends('template.app')

@section('title', 'Cotizaciones')

@section('content')
<nav class="mb-2">
  <ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="{{ url('/') }}">Inicio</a></li>
    <li class="breadcrumb-item active">Cotizaciones</li>
  </ol>
</nav>

<div class="card">
	<div class="card-header d-flex justify-content-between flex-column flex-sm-row gap-2">
		<div>
			<button class="btn btn-red" data-bs-toggle="modal" data-bs-target="#createModal">
				<i class="ti ti-plus icon"></i> Crear nuevo
			</button>
			<a href="{{ route('export', ['module' => 'quotations', 'format' => 'pdf']) }}" class="btn btn-outline-danger" target="_blank" data-bs-toggle="tooltip" title="Exportar a PDF">
				<i class="ti ti-file-type-pdf icon"></i> PDF
			</a>
			<a href="{{ route('export', ['module' => 'quotations', 'format' => 'excel']) }}" class="btn btn-outline-success" data-bs-toggle="tooltip" title="Exportar a Excel">
				<i class="ti ti-file-spreadsheet icon"></i> Excel
			</a>
		</div>
		<div>
			<form>
				<div class="input-group">
					<input type="text" class="form-control" placeholder="Buscar" name="search" value="{{ request()->search }}">
					<button type="submit" class="btn btn btn-icon">
						<i class="ti ti-search icon"></i>
					</button>
				</div>
			</form>
		</div>
	</div>
	<div class="table-responsive">
		<table class="table card-table table-vcenter">
			<thead>
				<tr>
					<th>Nombre</th>
					<th>Teléfono</th>
					<th>Paquete</th>
					<th>Número de personas</th>
					<th>Fecha de evento</th>
					<th>Fecha de respuesta</th>
					<th>Fecha</th>
					<th>Acción</th>
				</tr>
			</thead>
			<tbody>
				@if($quotations->count() > 0)
				@foreach($quotations as $quotation)
				<tr>
					<td>{{ $quotation->name }}</td>
					<td>{{ $quotation->phone }}</td>
					<td>{{ optional($quotation->package)->name }}</td>
					<td>{{ $quotation->people_number }}</td>
					<td>{{ $quotation->event_date->format('d/m/Y') }}</td>
					<td>{{ $quotation->answer_date->format('d/m/Y') }}</td>
					<td>{{ $quotation->date->format('d/m/Y H:i') }}</td>
					<td>
						<div class="d-flex gap-2">
							<div class="d-flex gap-2">
								<button class="btn btn-icon btn-primary btn-edit " data-id="{{ $quotation->id }}" data-bs-toggle="tooltip" title="Editar">
									<i class="ti ti-pencil icon"></i>
								</button>
								<a href="{{ route('quotations.pdf', $quotation) }}" class="btn btn-icon btn-primary" target="_blank" title="PDF">
									<i class="ti ti-file-invoice icon"></i>
								</a>
								<button class="btn btn-icon btn-red btn-delete" data-id="{{ $quotation->id }}" data-bs-toggle="tooltip" title="Eliminar">
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
	@if($quotations->hasPages())
	<div class="card-footer d-flex align-items-center">
		{{ $quotations->withQueryString()->links() }}
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
  			  			<label class="form-label required">Nombre</label>
  			  			<input type="text" class="form-control" name="name" autocomplete="off">
  			  		</div>
  			  	</div>
  			  	<div class="col-lg-6">
  			  		<div class="mb-3">
  			  			<label class="form-label required">Teléfono</label>
  			  			<input type="text" class="form-control" name="phone" autocomplete="off">
  			  		</div>
  			  	</div>
  			  	<div class="col-lg-6">
  			  		<div class="mb-3">
  			  			<label class="form-label required">Paquete</label>
  			  			<select class="form-select" name="package_id">
  			  				<option value="">Seleccionar</option>
  			  				@foreach($packages as $package)
  			  				<option value="{{ $package->id }}">{{ $package->name }}</option>
  			  				@endforeach
  			  			</select>
  			  		</div>
  			  	</div>
  			  	<div class="col-lg-6">
  			  		<div class="mb-3">
  			  			<label class="form-label required">Número de personas</label>
  			  			<input type="text" class="form-control" name="people_number">
  			  		</div>
  			  	</div>
  			  	<div class="col-lg-6">
  			  		<div class="mb-3">
  			  			<label class="form-label required">Fecha de cotización</label>
  			  			<input type="date" class="form-control" name="date" value="{{ date('Y-m-d') }}">
  			  		</div>
  			  	</div>
  			  	<div class="col-lg-6">
  			  		<div class="mb-3">
  			  			<label class="form-label required">Fecha de evento</label>
  			  			<input type="date" class="form-control" name="event_date">
  			  		</div>
  			  	</div>
  			  	<div class="col-lg-6">
  			  		<div class="mb-3">
  			  			<label class="form-label">Fecha de visita</label>
  			  			<input type="date" class="form-control" name="visit_date">
  			  		</div>
  			  	</div>
  			  	<div class="col-lg-6">
  			  		<div class="mb-3">
  			  			<label class="form-label required">Fecha de respuesta</label>
  			  			<input type="date" class="form-control" name="answer_date">
  			  		</div>
  			  	</div>
  			  	<div class="col-lg-12">
  			  		<div class="mb-3">
  			  			<label class="form-label">Observaciones</label>
  			  			<textarea class="form-control" name="observations" rows="3"></textarea>
  			  		</div>
  			  	</div>
  			  </div>
  			</div>
  			<div class="modal-footer">
  			  <button type="button" class="btn me-auto" data-bs-dismiss="modal"><i class="ti ti-x icon"></i> Cerrar</button>
  			  <button type="submit" class="btn btn-primary"><i class="ti ti-device-floppy icon"></i> Guardar</button>
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
  			  			<label class="form-label required">Nombre</label>
  			  			<input type="text" class="form-control" name="name" id="editName" autocomplete="off">
  			  		</div>
  			  	</div>
  			  	<div class="col-lg-6">
  			  		<div class="mb-3">
  			  			<label class="form-label required">Teléfono</label>
  			  			<input type="text" class="form-control" name="phone" id="editPhone" autocomplete="off">
  			  		</div>
  			  	</div>
  			  	<div class="col-lg-6">
  			  		<div class="mb-3">
  			  			<label class="form-label required">Paquete</label>
  			  			<select class="form-select" name="package_id" id="editPackageId">
  			  				<option value="">Seleccionar</option>
  			  				@foreach($packages as $package)
  			  				<option value="{{ $package->id }}">{{ $package->name }}</option>
  			  				@endforeach
  			  			</select>
  			  		</div>
  			  	</div>
  			  	<div class="col-lg-6">
  			  		<div class="mb-3">
  			  			<label class="form-label required">Número de personas</label>
  			  			<input type="text" class="form-control" name="people_number" id="editPeopleNumber">
  			  		</div>
  			  	</div>
  			  	<div class="col-lg-6">
  			  		<div class="mb-3">
  			  			<label class="form-label required">Fecha de cotización</label>
  			  			<input type="date" class="form-control" name="date" id="editDate">
  			  		</div>
  			  	</div>
  			  	<div class="col-lg-6">
  			  		<div class="mb-3">
  			  			<label class="form-label required">Fecha de evento</label>
  			  			<input type="date" class="form-control" name="event_date" id="editEventDate">
  			  		</div>
  			  	</div>
  			  	<div class="col-lg-6">
  			  		<div class="mb-3">
  			  			<label class="form-label">Fecha de visita</label>
  			  			<input type="date" class="form-control" name="visit_date" id="editVisitDate">
  			  		</div>
  			  	</div>
  			  	<div class="col-lg-6">
  			  		<div class="mb-3">
  			  			<label class="form-label required">Fecha de respuesta</label>
  			  			<input type="date" class="form-control" name="answer_date" id="editAnswerDate">
  			  		</div>
  			  	</div>
  			  	<div class="col-lg-12">
  			  		<div class="mb-3">
  			  			<label class="form-label">Observaciones</label>
  			  			<textarea class="form-control" name="observations" id="editObservations" rows="3"></textarea>
  			  		</div>
  			  	</div>
  			  </div>
  			</div>
  			<div class="modal-footer">
  				<input type="hidden" id="editId">
  			  <button type="button" class="btn me-auto" data-bs-dismiss="modal"><i class="ti ti-x icon"></i> Cerrar</button>
  			  <button type="submit" class="btn btn-primary"><i class="ti ti-device-floppy icon"></i> Guardar</button>
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
			url: '{{ route('quotations.store') }}',
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
			url: '{{ route('quotations.index') }}' + '/' + id + '/edit/',
			method: 'GET',
			success: function(data){
				$('#editName').val(data.name);
				$('#editPhone').val(data.phone);
				$('#editPackageId').val(data.package_id);
				$('#editPeopleNumber').val(data.people_number);
				$('#editDate').val(data.date);
				$('#editEventDate').val(data.event_date);
				$('#editVisitDate').val(data.visit_date);
				$('#editAnswerDate').val(data.answer_date);
				$('#editObservations').val(data.observations);
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
			url: '{{ route('quotations.index') }}' + '/' + id + '',
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
					url: '{{ route('quotations.index') }}' + '/' + id,
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