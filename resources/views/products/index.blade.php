@extends('template.app')

@section('title', 'Almacen')

@section('content')
<nav class="mb-2">
  <ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="{{ url('/') }}">Inicio</a></li>
    <li class="breadcrumb-item active">Almacen</li>
  </ol>
</nav>

<div class="card">
	<div class="card-header d-flex justify-content-between flex-column flex-sm-row gap-2">
		<div>
			<button class="btn btn-red" data-bs-toggle="modal" data-bs-target="#createModal">
				<i class="ti ti-plus icon"></i> Crear nuevo
			</button>
			<a href="{{ route('export', ['module' => 'products', 'format' => 'pdf']) }}" class="btn btn-outline-danger" target="_blank" data-bs-toggle="tooltip" title="Exportar a PDF">
				<i class="ti ti-file-type-pdf icon"></i> PDF
			</a>
			<a href="{{ route('export', ['module' => 'products', 'format' => 'excel']) }}" class="btn btn-outline-success" data-bs-toggle="tooltip" title="Exportar a Excel">
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
	<div class="card-body border-bottom">
		<form>
			<div class="row">
				<div class="col-lg-3">
					<div class="mb-3">
						<label class="form-label">Tipo</label>
						<select class="form-select" name="product_type_id">
							<option value="">Seleccionar</option>
							@foreach($product_types as $product_type)
							<option value="{{ $product_type->id }}" @if(request()->product_type_id == $product_type->id) selected @endif>{{ $product_type->name }}</option>
							@endforeach
						</select>
					</div>
				</div>
			</div>
			<button type="submit" class="btn btn-primary">
				Filtrar
			</button>
			<a href="{{ route('products.index') }}" class="btn btn-danger">Limpiar</a>
		</form>
	</div>
	<div class="table-responsive">
		<table class="table card-table table-vcenter">
			<thead>
				<tr>
					<th>Código</th>
					<th>Nombre</th>
					<th>Tipo</th>
					<th>Ubicación</th>
					<th>Stock</th>
					<th>Acción</th>
				</tr>
			</thead>
			<tbody>
				@if($products->count() > 0)
				@foreach($products as $product)
				<tr>
					<td>{{ $product->code }}</td>
					<td>{{ $product->name }}</td>
					<td>{{ optional($product->product_type)->name }}</td>
					<td>{{ $product->location }}</td>
					<td>{{ $product->stock }}</td>
					<td>
						<div class="d-flex gap-2">
							<div class="d-flex gap-2">
								<button class="btn btn-icon btn-primary btn-edit " data-id="{{ $product->id }}" data-bs-toggle="tooltip" title="Editar">
									<i class="ti ti-pencil icon"></i>
								</button>
								<button class="btn btn-icon btn-dark btn-decrement" data-id="{{ $product->id }}" data-bs-toggle="tooltip" title="Disminuir stock">
									<i class="ti ti-minus icon"></i>
								</button>
								<button class="btn btn-icon btn-dark btn-increment" data-id="{{ $product->id }}" data-bs-toggle="tooltip" title="Aumentar stock">
									<i class="ti ti-plus icon"></i>
								</button>
								<button class="btn btn-icon btn-primary btn-movements" data-id="{{ $product->id }}" data-bs-toggle="tooltip" title="Movimientos">
									<i class="ti ti-truck icon"></i>
								</button>
								<button class="btn btn-icon btn-red btn-delete" data-id="{{ $product->id }}" data-bs-toggle="tooltip" title="Eliminar">
									<i class="ti ti-x icon"></i>
								</button>
							</div>
						</div>
					</td>		
				</tr>
				@endforeach
				@else
				<tr>
					<td colspan="6" align="center">No se han encontrado resultados</td>
				</tr>
				@endif
			</tbody>
		</table>
	</div>
	@if($products->hasPages())
	<div class="card-footer d-flex align-items-center">
		{{ $products->withQueryString()->links() }}
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
  			  			<label class="form-label required">Tipo de producto</label>
  			  			<select class="form-select" name="product_type_id">
  			  				<option value="">Seleccionar</option>
  			  				@foreach($product_types as $product_type)
  			  				<option value="{{ $product_type->id }}">{{ $product_type->name }}</option>
  			  				@endforeach
  			  			</select>
  			  		</div>
  			  	</div>
  			  	<div class="col-lg-6">
  			  		<div class="mb-3">
  			  			<label class="form-label required">Ubicación</label>
  			  			<select type="text" class="form-select" name="location">
  			  				<option value="">Seleccionar</option>
  			  				<option value="Almacen">Almacen</option>
  			  				<option value="Salon">Salon</option>
  			  				<option value="Cocina">Cocina</option>
  			  			</select>
  			  		</div>
  			  	</div>
  			  	<div class="col-lg-6">
  			  		<div class="mb-3">
  			  			<label class="form-label required">Stock</label>
  			  			<input type="text" class="form-control" name="stock" autocomplete="off">
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
  			  			<label class="form-label required">Tipo de almacen</label>
  			  			<select class="form-select" name="product_type_id" id="editProductTypeId">
  			  				<option value="">Seleccionar</option>
  			  				@foreach($product_types as $product_type)
  			  				<option value="{{ $product_type->id }}">{{ $product_type->name }}</option>
  			  				@endforeach
  			  			</select>
  			  		</div>
  			  	</div>
  			  	<div class="col-lg-6">
  			  		<div class="mb-3">
  			  			<label class="form-label required">Ubicación</label>
  			  			<select type="text" class="form-select" name="location" id="editLocation">
  			  				<option value="">Seleccionar</option>
  			  				<option value="Almacen">Almacen</option>
  			  				<option value="Salon">Salon</option>
  			  				<option value="Cocina">Cocina</option>
  			  			</select>
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

<div class="modal modal-blur fade" id="decrementModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
  	<div class="modal-content">
  		<form id="decrementForm" method="POST">
  			<div class="modal-header">
  			  <h5 class="modal-title">Registrar salida</h5>
  			  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
  			</div>
  			<div class="modal-body">
  			  <div class="row">
  			  	<div class="col-lg-6">
  			  		<div class="mb-3">
  			  			<label class="form-label required">Nombre</label>
  			  			<input type="text" class="form-control" id="decrementName" disabled autocomplete="off">
  			  		</div>
  			  	</div>
  			  	<div class="col-lg-6">
  			  		<div class="mb-3">
  			  			<label class="form-label required">Cantidad</label>
  			  			<input type="text" class="form-control" name="quantity" autocomplete="off">
  			  		</div>
  			  	</div>
  			  </div>
  			</div>
  			<div class="modal-footer">
  				<input type="hidden" id="decrementId">
  			  <button type="button" class="btn me-auto" data-bs-dismiss="modal"><i class="ti ti-x icon"></i> Cerrar</button>
  			  <button type="submit" class="btn btn-primary"><i class="ti ti-device-floppy icon"></i> Guardar</button>
  			</div>
  		</form>
    </div>
  </div>
</div>

<div class="modal modal-blur fade" id="incrementModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
  	<div class="modal-content">
  		<form id="incrementForm" method="POST">
  			<div class="modal-header">
  			  <h5 class="modal-title">Registrar entrada</h5>
  			  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
  			</div>
  			<div class="modal-body">
  			  <div class="row">
  			  	<div class="col-lg-6">
  			  		<div class="mb-3">
  			  			<label class="form-label required">Nombre</label>
  			  			<input type="text" class="form-control" id="incrementName" disabled autocomplete="off">
  			  		</div>
  			  	</div>
  			  	<div class="col-lg-6">
  			  		<div class="mb-3">
  			  			<label class="form-label required">Cantidad</label>
  			  			<input type="text" class="form-control" name="quantity" autocomplete="off">
  			  		</div>
  			  	</div>
  			  </div>
  			</div>
  			<div class="modal-footer">
  				<input type="hidden" id="incrementId">
  			  <button type="button" class="btn me-auto" data-bs-dismiss="modal"><i class="ti ti-x icon"></i> Cerrar</button>
  			  <button type="submit" class="btn btn-primary"><i class="ti ti-device-floppy icon"></i> Guardar</button>
  			</div>
  		</form>
    </div>
  </div>
</div>

<div class="modal modal-blur fade" id="movementsModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
  	<div class="modal-content">
			<div class="modal-header">
			  <h5 class="modal-title">Movimientos</h5>
			  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body">
				<div class="mb-3">
					<label class="form-label">Nombre</label>
					<input type="text" class="form-control" id="name" disabled>
				</div>
			  <table class="table table-bordered">
			  	<thead class="table-light">
			  		<tr>
			  			<th>Tipo</th>
			  			<th>Cantidad</th>
			  			<th>Stock</th>
			  			<th>Fecha</th>
			  		</tr>
			  	</thead>
			  	<tbody id="tbl-movements"></tbody>
			  </table>
			</div>
    </div>
  </div>
</div>
@endsection

@section('scripts')
<script>

	$('#storeForm').submit(function(e){
		e.preventDefault();

		$.ajax({
			url: '{{ route('products.store') }}',
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
			url: '{{ route('products.index') }}' + '/' + id + '/edit/',
			method: 'GET',
			success: function(data){
				$('#editName').val(data.name);
				$('#editProductTypeId').val(data.product_type_id);
				$('#editLocation').val(data.location);
				$('#editStock').val(data.stock);
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
			url: '{{ route('products.index') }}' + '/' + id + '',
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
					url: '{{ route('products.index') }}' + '/' + id,
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

	$(document).on('click', '.btn-decrement', function(){

		var id = $(this).data('id');

		$.ajax({
			url: '{{ route('products.index') }}' + '/' + id + '/edit/',
			method: 'GET',
			success: function(data){
				$('#decrementName').val(data.name);
				$('#decrementId').val(data.id);
				$('#decrementModal').modal('show');
			},
			error: function(err){
				ToastError.fire({ text: 'Ocurrió un error' });
			}
		});

	});

	$(document).on('click', '.btn-increment', function(){

		var id = $(this).data('id');

		$.ajax({
			url: '{{ route('products.index') }}' + '/' + id + '/edit/',
			method: 'GET',
			success: function(data){
				$('#incrementName').val(data.name);
				$('#incrementId').val(data.id);
				$('#incrementModal').modal('show');
			},
			error: function(err){
				ToastError.fire({ text: 'Ocurrió un error' });
			}
		});

	});

	$('#decrementForm').submit(function(e){
		e.preventDefault();

		var id = $('#decrementId').val();

		$.ajax({
			url: '{{ route('products.index') }}' + '/' + id + '/decrement',
			method: 'POST',
			data: $(this).serialize(),
			success: function(data){
				if(data.status){
					$('#decrementModal').modal('hide');
					$('#decrementForm')[0].reset();

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

	$('#incrementForm').submit(function(e){
		e.preventDefault();

		var id = $('#incrementId').val();

		$.ajax({
			url: '{{ route('products.index') }}' + '/' + id + '/increment',
			method: 'POST',
			data: $(this).serialize(),
			success: function(data){
				if(data.status){
					$('#incrementModal').modal('hide');
					$('#incrementForm')[0].reset();

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

	$(document).on('click', '.btn-movements', function(){

		var id = $(this).data('id');

		$.ajax({
			url: '{{ route('products.index') }}' + '/' + id + '/movements',
			method: 'GET',
			success: function(data){
				var html = '';

				data.movements.forEach(function(movement){
					
					html += `
						<tr>
							<td>${movement.type}</td>
							<td>${movement.quantity}</td>
							<td>${movement.stock}</td>
							<td>${movement.date}</td>
						</tr>
					`;

				});

				$('#name').val(data.name);
				$('#tbl-movements').html(html);
				$('#movementsModal').modal('show');
			},
			error: function(err){
				ToastError.fire({ text: 'Ocurrió un error' });
			}
		});

	});




</script>
@endsection