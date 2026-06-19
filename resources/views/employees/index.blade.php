@extends('template.app')

@section('title', 'Personal')

@section('content')
<nav class="mb-2">
	<ol class="breadcrumb">
		<li class="breadcrumb-item"><a href="{{ url('/') }}">Inicio</a></li>
		<li class="breadcrumb-item active">Personal</li>
	</ol>
</nav>
<div class="card">
	<div class="card-header d-flex justify-content-between flex-column flex-sm-row gap-2">
		<div>
			<button class="btn btn-red" data-bs-toggle="modal" data-bs-target="#createModal">
				<i class="ti ti-plus icon"></i> Crear nuevo
			</button>
			<a href="{{ route('export', ['module' => 'employees', 'format' => 'pdf']) }}" class="btn btn-outline-danger" target="_blank" data-bs-toggle="tooltip" title="Exportar a PDF">
				<i class="ti ti-file-type-pdf icon"></i> PDF
			</a>
			<a href="{{ route('export', ['module' => 'employees', 'format' => 'excel']) }}" class="btn btn-outline-success" data-bs-toggle="tooltip" title="Exportar a Excel">
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
					<th>DNI</th>
					<th>Nombre</th>
					<th>Puesto</th>
					<th>Función</th>
					<th>Teléfono</th>
					<th>Acción</th>
				</tr>
			</thead>
			<tbody>
				@if($employees->count() > 0)
				@foreach($employees as $employee)
				<tr>
					<td>{{ $employee->document }}</td>
					<td>{{ $employee->name }}</td>
					<td>{{ $employee->job }}</td>
					<td>{{ $employee->function }}</td>
					<td>{{ $employee->phone }}</td>
					<td>
						<div class="d-flex gap-2">
							<div class="d-flex gap-2">
								<button class="btn btn-icon btn-primary btn-edit" data-id="{{ $employee->id }}" data-bs-toggle="tooltip" title="Editar">
									<i class="ti ti-pencil icon"></i>
								</button>
								<button class="btn btn-icon btn-red btn-delete" data-id="{{ $employee->id }}" data-bs-toggle="tooltip" title="Eliminar">
									<i class="ti ti-x icon"></i>
								</button>
							</div>
						</div>
					</td>		
				</tr>
				@endforeach
				@else
				<tr>
					<td colspan="5" align="center">No se han encontrado resultados</td>
				</tr>
				@endif
			</tbody>
		</table>
	</div>
	@if($employees->hasPages())
	<div class="card-footer d-flex align-items-center">
		{{ $employees->withQueryString()->links() }}
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
								<label class="form-label required">DNI</label>
								<div class="input-group">
									<input type="text" class="form-control" name="document" id="createDocument" autocomplete="off">
									<button class="btn btn-outline-secondary btn-search-dni" type="button" data-target="create">
										<i class="ti ti-search icon"></i>
									</button>
								</div>
							</div>
						</div>
						<div class="col-lg-6">
							<div class="mb-3">
								<label class="form-label required">Nombre</label>
								<input type="text" class="form-control" name="name" id="createName" autocomplete="off">
							</div>
						</div>
						<div class="col-lg-6">
							<div class="mb-3">
								<label class="form-label">Puesto</label>
								<input type="text" class="form-control" name="job" autocomplete="off">
							</div>
						</div>
						<div class="col-lg-6">
							<div class="mb-3">
								<label class="form-label">Función</label>
								<input type="text" class="form-control" name="function" autocomplete="off">
							</div>
						</div>
						<div class="col-lg-6">
							<div class="mb-3">
								<label class="form-label">Teléfono</label>
								<input type="text" class="form-control" name="phone" id="createPhone" autocomplete="off">
							</div>
						</div>
						<div class="col-lg-12">
							<hr>
							<div class="form-check mb-3">
								<input class="form-check-input" type="checkbox" value="1" name="has_user" id="checkHasUser">
								<label class="form-check-label" for="checkHasUser">
									Asignar acceso al sistema (Crear usuario)
								</label>
							</div>
						</div>
						<div class="col-lg-12" id="userFields" style="display: none;">
							<div class="row">
								<div class="col-lg-4">
									<div class="mb-3">
										<label class="form-label required">Usuario</label>
										<input type="text" class="form-control" name="username" autocomplete="off">
									</div>
								</div>
								<div class="col-lg-4">
									<div class="mb-3">
										<label class="form-label required">Contraseña</label>
										<input type="password" class="form-control" name="password" autocomplete="new-password">
									</div>
								</div>
								<div class="col-lg-4">
									<div class="mb-3">
										<label class="form-label required">Rol en sistema</label>
										<select class="form-select" name="role">
											<option value="">Seleccione</option>
											<option value="admin">Administrador</option>
											<option value="ventas">Ventas</option>
											<option value="compras">Compras / Finanzas</option>
											<option value="almacen">Almacén</option>
										</select>
									</div>
								</div>
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
								<label class="form-label required">DNI</label>
								<div class="input-group">
									<input type="text" class="form-control" name="document" id="editDocument" autocomplete="off">
									<button class="btn btn-outline-secondary btn-search-dni" type="button" data-target="edit">
										<i class="ti ti-search icon"></i>
									</button>
								</div>
							</div>
						</div>
						<div class="col-lg-6">
							<div class="mb-3">
								<label class="form-label required">Nombre</label>
								<input type="text" class="form-control" name="name" id="editName" autocomplete="off">
							</div>
						</div>
						<div class="col-lg-6">
							<div class="mb-3">
								<label class="form-label">Puesto</label>
								<input type="text" class="form-control" name="job" id="editJob" autocomplete="off">
							</div>
						</div>
						<div class="col-lg-6">
							<div class="mb-3">
								<label class="form-label">Función</label>
								<input type="text" class="form-control" name="function" id="editFunction" autocomplete="off">
							</div>
						</div>
						<div class="col-lg-6">
							<div class="mb-3">
								<label class="form-label">Teléfono</label>
								<input type="text" class="form-control" name="phone" id="editPhone" autocomplete="off">
							</div>
						</div>
						<div class="col-lg-12">
							<hr>
							<div class="form-check mb-3">
								<input class="form-check-input" type="checkbox" value="1" name="has_user" id="editCheckHasUser">
								<label class="form-check-label" for="editCheckHasUser">
									Asignar acceso al sistema (Crear usuario)
								</label>
							</div>
						</div>
						<div class="col-lg-12" id="editUserFields" style="display: none;">
							<div class="row">
								<div class="col-lg-4">
									<div class="mb-3">
										<label class="form-label required">Usuario</label>
										<input type="text" class="form-control" name="username" id="editUsername" autocomplete="off">
									</div>
								</div>
								<div class="col-lg-4">
									<div class="mb-3">
										<label class="form-label">Contraseña <small>(dejar vacío para no cambiar)</small></label>
										<input type="password" class="form-control" name="password" autocomplete="new-password">
									</div>
								</div>
								<div class="col-lg-4">
									<div class="mb-3">
										<label class="form-label required">Rol en sistema</label>
										<select class="form-select" name="role" id="editRole">
											<option value="">Seleccione</option>
											<option value="admin">Administrador</option>
											<option value="ventas">Ventas</option>
											<option value="compras">Compras / Finanzas</option>
											<option value="almacen">Almacén</option>
										</select>
									</div>
								</div>
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

	$('#checkHasUser').change(function(){
		if($(this).is(':checked')){
			$('#userFields').show();
		}else{
			$('#userFields').hide();
		}
	});

	$('#editCheckHasUser').change(function(){
		if($(this).is(':checked')){
			$('#editUserFields').show();
		}else{
			$('#editUserFields').hide();
		}
	});

	$('#storeForm').submit(function(e){
		e.preventDefault();

		$.ajax({
			url: '{{ route('employees.store') }}',
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
			url: '{{ route('employees.index') }}' + '/' + id + '/edit/',
			method: 'GET',
			success: function(data){
				$('#editDocument').val(data.document);
				$('#editName').val(data.name);
				$('#editJob').val(data.job);
				$('#editFunction').val(data.function);
				$('#editPhone').val(data.phone);
				$('#editId').val(data.id);
				
				if(data.user) {
					$('#editCheckHasUser').prop('checked', true);
					$('#editUserFields').show();
					$('#editUsername').val(data.user.user);
					$('#editRole').val(data.user.role);
				} else {
					$('#editCheckHasUser').prop('checked', false);
					$('#editUserFields').hide();
					$('#editUsername').val('');
					$('#editRole').val('');
				}

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
			url: '{{ route('employees.index') }}' + '/' + id + '',
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
					url: '{{ route('employees.index') }}' + '/' + id,
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

	$('.btn-search-dni').click(function(){
		var target = $(this).data('target');
		var dni = target == 'create' ? $('#createDocument').val() : $('#editDocument').val();
		var btn = $(this);

		if(dni.length != 8){
			ToastError.fire({ text: 'El DNI debe tener 8 dígitos' });
			return;
		}

		btn.prop('disabled', true);
		btn.html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>');

		$.ajax({
			url: '{{ route('search.dni') }}',
			method: 'GET',
			data: { numero: dni },
			success: function(data){
				btn.prop('disabled', false);
				btn.html('<i class="ti ti-search icon"></i>');

				if(data.status){
					var fullName = data.nombres + ' ' + data.apellidoPaterno + ' ' + data.apellidoMaterno;
					if (target == 'create') {
						$('#createName').val(fullName);
					} else {
						$('#editName').val(fullName);
					}
				}else{
					ToastError.fire({ text: data.error ? data.error : 'Ocurrió un error' });
				}
			},
			error: function(err){
				btn.prop('disabled', false);
				btn.html('<i class="ti ti-search icon"></i>');
				ToastError.fire({ text: 'Ocurrió un error en la conexión' });
			}
		});
	});

</script>
@endsection