@extends('template.app')

@section('title', 'Locaciones')

@section('content')
<nav class="mb-2">
  <ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="{{ url('/') }}">Inicio</a></li>
    <li class="breadcrumb-item active">Locaciones</li>
  </ol>
</nav>

<div class="card">
	<div class="card-header d-flex justify-content-between flex-column flex-sm-row gap-2">
		<div>
			<button class="btn btn-red" data-bs-toggle="modal" data-bs-target="#createModal">
				<i class="ti ti-plus icon"></i> Crear nuevo
			</button>
			<a href="{{ route('export', ['module' => 'locations', 'format' => 'pdf']) }}" class="btn btn-outline-danger" target="_blank" data-bs-toggle="tooltip" title="Exportar a PDF">
				<i class="ti ti-file-type-pdf icon"></i> PDF
			</a>
			<a href="{{ route('export', ['module' => 'locations', 'format' => 'excel']) }}" class="btn btn-outline-success" data-bs-toggle="tooltip" title="Exportar a Excel">
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
					<th>Acción</th>
				</tr>
			</thead>
			<tbody>
				@if($locations->count() > 0)
				@foreach($locations as $location)
				<tr>
					<td>{{ $location->name }}</td>
					<td>
						<div class="d-flex gap-2">
							<div class="d-flex gap-2">
								<button class="btn btn-icon btn-primary btn-edit " data-id="{{ $location->id }}" data-bs-toggle="tooltip" title="Editar">
									<i class="ti ti-pencil icon"></i>
								</button>
								<button class="btn btn-icon btn-info btn-sublocations" data-id="{{ $location->id }}" data-name="{{ $location->name }}" data-bs-toggle="tooltip" title="Administrar Lados">
									<i class="ti ti-list icon"></i> Lados
								</button>
								<button class="btn btn-icon btn-red btn-delete" data-id="{{ $location->id }}" data-bs-toggle="tooltip" title="Eliminar">
									<i class="ti ti-x icon"></i>
								</button>
							</div>
						</div>
					</td>		
				</tr>
				@endforeach
				@else
				<tr>
					<td colspan="2" align="center">No se han encontrado resultados</td>
				</tr>
				@endif
			</tbody>
		</table>
	</div>
	@if($locations->hasPages())
	<div class="card-footer d-flex align-items-center">
		{{ $locations->withQueryString()->links() }}
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
  			  			<label class="form-label">Nombre</label>
  			  			<input type="text" class="form-control" name="name" autocomplete="off">
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
  			  			<label class="form-label">Nombre</label>
  			  			<input type="text" class="form-control" name="name" id="editName" autocomplete="off">
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

<div class="modal modal-blur fade" id="sublocationsModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
  	<div class="modal-content">
  		<form id="storeSublocationForm" method="POST">
  			<div class="modal-header">
  			  <h5 class="modal-title">Administrar Lados - <span id="sublocationLocationName"></span></h5>
  			  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
  			</div>
  			<div class="modal-body">
  			  <div class="row">
  			  	<div class="col-lg-8">
  			  		<div class="mb-3">
  			  			<label class="form-label">Nombre del Lado</label>
  			  			<input type="text" class="form-control" name="name" autocomplete="off" required>
						<input type="hidden" name="location_id" id="sublocationLocationId">
  			  		</div>
  			  	</div>
				<div class="col-lg-4 d-flex align-items-end">
					<div class="mb-3 w-100">
						<button type="submit" class="btn btn-primary w-100"><i class="ti ti-plus icon"></i> Agregar</button>
					</div>
				</div>
  			  </div>
			  
			  <div class="table-responsive mt-3">
				<table class="table table-vcenter table-bordered">
					<thead>
						<tr>
							<th>Lado</th>
							<th width="10%">Acción</th>
						</tr>
					</thead>
					<tbody id="tbl-sublocations">
					</tbody>
				</table>
			  </div>
  			</div>
  			<div class="modal-footer">
  			  <button type="button" class="btn me-auto" data-bs-dismiss="modal"><i class="ti ti-x icon"></i> Cerrar</button>
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
			url: '{{ route('locations.store') }}',
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
			url: '{{ route('locations.index') }}' + '/' + id + '/edit/',
			method: 'GET',
			success: function(data){
				$('#editName').val(data.name);
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
			url: '{{ route('locations.index') }}' + '/' + id + '',
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
					url: '{{ route('locations.index') }}' + '/' + id,
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

	// Sublocations logic
	$(document).on('click', '.btn-sublocations', function(){
		var locationId = $(this).data('id');
		var locationName = $(this).data('name');
		
		$('#sublocationLocationId').val(locationId);
		$('#sublocationLocationName').text(locationName);
		
		loadSublocations(locationId);
		
		$('#sublocationsModal').modal('show');
	});

	function loadSublocations(locationId){
		$.ajax({
			url: '{{ route('sublocations.index') }}',
			method: 'GET',
			data: { location_id: locationId },
			success: function(data){
				var html = '';
				if(data.length > 0){
					data.forEach(function(item){
						html += '<tr><td>' + item.name + '</td><td>' +
								'<button class="btn btn-icon btn-red btn-delete-sublocation" data-id="' + item.id + '" data-bs-toggle="tooltip" title="Eliminar">' +
								'<i class="ti ti-x icon"></i></button></td></tr>';
					});
				} else {
					html = '<tr><td colspan="2" class="text-center">No hay lados registrados</td></tr>';
				}
				$('#tbl-sublocations').html(html);
			}
		});
	}

	$('#storeSublocationForm').submit(function(e){
		e.preventDefault();

		$.ajax({
			url: '{{ route('sublocations.store') }}',
			method: 'POST',
			data: $(this).serialize(),
			success: function(data){
				if(data.status){
					$('#storeSublocationForm').find('input[name="name"]').val('');
					loadSublocations($('#sublocationLocationId').val());
					ToastMessage.fire({ text: 'Lado guardado exitosamente' });
				}else{
					ToastError.fire({ text: data.error ? data.error : 'Ocurrió un error' });
				}
			},
			error: function(err){
				ToastError.fire({ text: 'Ocurrió un error' });
			}
		});
	});

	$(document).on('click', '.btn-delete-sublocation', function(e){
		e.preventDefault();
		var id = $(this).data('id');

		ToastConfirm.fire({
			text: '¿Estás seguro que deseas eliminar este lado?',
		}).then((result) => {
			if(result.isConfirmed){
				$.ajax({
					url: '{{ url('sublocations') }}/' + id,
					method: 'DELETE',
					data: { _token: '{{ csrf_token() }}' },
					success: function(data){
						loadSublocations($('#sublocationLocationId').val());
						ToastMessage.fire({ text: 'Lado eliminado' });
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