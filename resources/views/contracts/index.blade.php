@extends('template.app')

@section('title', 'Contratos')

@section('content')
<nav class="mb-2">
  <ol class="breadcrumb">
    <li class="breadcrumb-item"><a href="{{ url('/') }}">Inicio</a></li>
    <li class="breadcrumb-item active">Contratos</li>
  </ol>
</nav>

<div class="card">
	<div class="card-header d-flex justify-content-between flex-column flex-sm-row gap-2">
		<div>
			<button class="btn btn-red" data-bs-toggle="modal" data-bs-target="#createModal">
				<i class="ti ti-plus icon"></i> Crear nuevo
			</button>
			<a href="{{ route('contracts.charges') }}" class="btn btn-success">
				<i class="ti ti-cash icon"></i> Cobranzas
			</a>
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
			<a href="{{ route('contracts.index') }}" class="btn btn-danger">Limpiar</a>
		</form>
	</div>
	<div class="table-responsive">
		<table class="table card-table table-vcenter">
			<thead>
				<tr>
					<th>DNI</th>
					<th>Código</th>
					<th>Nombre</th>
					<th>Tipo de evento</th>
					<th>Fecha de evento</th>
					<th>Duración</th>
					<th>Paquete</th>
					<th>Personas</th>
					<th>Descuento</th>
					<th>Total</th>
					<th></th>
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
					<td>{{ optional($contract->event_type)->name }}</td>
					<td>{{ $contract->event_date->format('d/m/Y').' '.$contract->event_time->format('H:i').'-'.$contract->event_end->format('H:i') }}</td>
					<td>{{ $contract->event_duration }} horas</td>
					<td>{{ optional($contract->package)->name }}</td>
					<td>{{ $contract->people_number }}</td>
					<td>S/{{ $contract->discount }}</td>
					<td>S/{{ $contract->total }}</td>
					<td>
						@if($contract->paid)
						<span class="badge bg-success"></span>
						@else
						<span class="badge bg-danger"></span>
						@endif
					</td>
					<td>
						<div class="d-flex gap-2">
							<div class="d-flex gap-2">
								<a href="{{ route('contracts.pdf', $contract) }}" class="btn btn-icon btn-primary" target="_blank" title="Contrato">
									<i class="ti ti-file-invoice icon"></i>
								</a>
								<button class="btn btn-icon btn-primary btn-edit" data-id="{{ $contract->id }}" title="Editar">
									<i class="ti ti-pencil icon"></i>
								</button>
								<button class="btn btn-icon btn-primary btn-extra" data-id="{{ $contract->id }}" title="Agregar extra">
									<i class="ti ti-list icon"></i>
								</button>
								<button class="btn btn-icon btn-primary btn-employee" data-id="{{ $contract->id }}" title="Agregar personal">
									<i class="ti ti-users icon"></i>
								</button>
								<button class="btn btn-icon btn-primary btn-schedule" data-id="{{ $contract->id }}" title="Agregar horario de personal">
									<i class="ti ti-clock icon"></i>
								</button>
								<a href="{{ route('contracts.pdf2', $contract) }}" class="btn btn-icon btn-primary" target="_blank" title="Reporte de personal">
									<i class="ti ti-printer icon"></i>
								</a>
								<button class="btn btn-icon btn-primary btn-total" data-id="{{ $contract->id }}" title="Editar total">
									<i class="ti ti-cash icon"></i>
								</button>
								<button class="btn btn-icon btn-red btn-delete" data-id="{{ $contract->id }}" title="Eliminar">
									<i class="ti ti-x icon"></i>
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

<div class="modal modal-blur fade" id="createModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
  	<div class="modal-content">
  		<form id="storeForm" method="POST">
  			<div class="modal-header">
  			  <h5 class="modal-title">Crear nuevo</h5>
  			  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
  			</div>
  			<div class="modal-body">
  			  <div class="row">
  			  	<div class="col-lg-4">
  			  		<div class="mb-3">
  			  			<label class="form-label required">DNI</label>
  			  			<input type="text" class="form-control" name="document" autocomplete="off">
  			  		</div>
  			  	</div>
  			  	<div class="col-lg-4">
  			  		<div class="mb-3">
  			  			<label class="form-label required">Nombre</label>
  			  			<input type="text" class="form-control" name="name" autocomplete="off">
  			  		</div>
  			  	</div>
  			  	<div class="col-lg-4">
  			  		<div class="mb-3">
  			  			<label class="form-label">RUC</label>
  			  			<input type="text" class="form-control" name="business_document" autocomplete="off">
  			  		</div>
  			  	</div>
  			  	<div class="col-lg-4">
  			  		<div class="mb-3">
  			  			<label class="form-label">Razón social</label>
  			  			<input type="text" class="form-control" name="business_name" autocomplete="off">
  			  		</div>
  			  	</div>
  			  	<div class="col-lg-4">
  			  		<div class="mb-3">
  			  			<label class="form-label">Teléfono</label>
  			  			<input type="text" class="form-control" name="phone" autocomplete="off">
  			  		</div>
  			  	</div>
  			  	<div class="col-lg-4">
  			  		<div class="mb-3">
  			  			<label class="form-label">Correo electrónico</label>
  			  			<input type="text" class="form-control" name="email" autocomplete="off">
  			  		</div>
  			  	</div>
  			  	<div class="col-lg-4">
  			  		<div class="mb-3">
  			  			<label class="form-label required">Locación</label>
  			  			<select class="form-select" name="location_id">
  			  				<option value="">Seleccionar</option>
  			  				@foreach($locations as $location)
  			  				<option value="{{ $location->id }}">{{ $location->name }}</option>
  			  				@endforeach
  			  			</select>
  			  		</div>
  			  	</div>
  			  	<div class="col-lg-4">
  			  		<div class="mb-3">
  			  			<label class="form-label required">Tipo de evento</label>
  			  			<select class="form-select" name="event_type_id">
  			  				<option value="">Seleccionar</option>
  			  				@foreach($event_types as $event_type)
  			  				<option value="{{ $event_type->id }}">{{ $event_type->name }}</option>
  			  				@endforeach
  			  			</select>
  			  		</div>
  			  	</div>
  			  	<div class="col-lg-4">
  			  		<div class="mb-3">
  			  			<label class="form-label required">Fecha de evento</label>
  			  			<input type="date" class="form-control" name="event_date">
  			  		</div>
  			  	</div>
  			  	<div class="col-lg-4">
  			  		<div class="mb-3">
  			  			<label class="form-label required">Hora de inicio evento</label>
  			  			<input type="time" class="form-control" name="event_time">
  			  		</div>
  			  	</div>
  			  	<div class="col-lg-4">
  			  		<div class="mb-3">
  			  			<label class="form-label required">Duración de evento (horas)</label>
  			  			<input type="text" class="form-control" name="event_duration">
  			  		</div>
  			  	</div>
  			  	<div class="col-lg-4">
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
  			  	<div class="col-lg-4">
  			  		<div class="mb-3">
  			  			<label class="form-label required">Número de personas</label>
  			  			<input type="text" class="form-control" name="people_number" autocomplete="off">
  			  		</div>
  			  	</div>
  			  	<div class="col-lg-12">
  			  		<button type="button" class="btn btn-primary mb-2" data-bs-toggle="collapse" data-bs-target="#collapse1"><i class="ti ti-plus icon"></i> Extras</button>
  			  		<button type="button" class="btn btn-primary mb-2" data-bs-toggle="collapse" data-bs-target="#collapse2"><i class="ti ti-plus icon"></i> Personal</button>
  			  	</div>
  			  	<div class="col-lg-12">
  			  		<div class="collapse" id="collapse1">
  			  			<label class="form-label">Extras</label>
  			  		  <table class="table table-bordered">
  			  		  	<thead>
  			  		  		<tr>
  			  		  			<th></th>
  			  		  			<th>Nombre</th>
  			  		  			<th>Precio</th>
  			  		  		</tr>
  			  		  	</thead>
  			  		  	<tbody>
  			  		  		@foreach($extras as $extra)
  			  		  		<tr>
  			  		  			<td><input type="checkbox" class="form-check-input" name="extra[]" value="{{ $extra->id }}"></td>
  			  		  			<td>{{ $extra->name }} </td>
  			  		  			<td>{{ $extra->price }}</td>
  			  		  		</tr>
  			  		  		@endforeach
  			  		  	</tbody>
  			  		  </table>
  			  		</div>
  			  	</div>
  			  	<div class="col-lg-12">
  			  		<div class="collapse" id="collapse2">
  			  			<label class="form-label">Personal</label>
  			  		  <table class="table table-bordered">
  			  		  	<thead>
  			  		  		<tr>
  			  		  			<th></th>
  			  		  			<th>Nombre</th>
  			  		  			<th>Puesto</th>
  			  		  		</tr>
  			  		  	</thead>
  			  		  	<tbody>
  			  		  		@foreach($employees as $employee)
  			  		  		<tr>
  			  		  			<td><input type="checkbox" class="form-check-input" name="employee[]" value="{{ $employee->id }}"></td>
  			  		  			<td>{{ $employee->name }} </td>
  			  		  			<td>{{ $employee->job }}</td>
  			  		  		</tr>
  			  		  		@endforeach
  			  		  	</tbody>
  			  		  </table>
  			  		</div>
  			  	</div>
  			  	<div class="col-lg-4">
  			  		<div class="mb-3">
  			  			<label class="form-label required">Tipo de descuento</label>
  			  			<select class="form-select" name="discount_type">
  			  				<option value="">Seleccionar</option>
  			  				<option value="Persona">Persona</option>
  			  				<option value="Evento">Evento</option>
  			  			</select>
  			  		</div>
  			  	</div>
  			  	<div class="col-lg-4">
  			  		<div class="mb-3">
  			  			<label class="form-label required">Descuento</label>
  			  			<input type="text" class="form-control" name="discount" value="0.00" autocomplete="off">
  			  		</div>
  			  	</div>
  			  	<div class="col-lg-4">
  			  		<div class="mb-3">
  			  			<label class="form-label required">Pago inicial</label>
  			  			<input type="text" class="form-control" name="initial_payment" value="0.00" autocomplete="off">
  			  		</div>
  			  	</div>
  			  	<div class="col-lg-4">
  			  		<div class="mb-3">
  			  			<label class="form-label required">Tipo de pago</label>
  			  			<select class="form-select" name="payment_type">
  			  				<option value="">Seleccionar</option>
  			  				<option value="Contado">Contado</option>
  			  				<option value="Crédito">Crédito</option>
  			  			</select>
  			  		</div>
  			  	</div>
  			  	<div class="col-lg-4">
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
  			  	<div class="col-lg-4">
  			  		<div class="mb-3">
  			  			<label class="form-label required">Fecha de pago de deuda</label>
  			  			<input type="date" class="form-control" name="debt_payment_date">
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
  							<input type="text" class="form-control" name="document" id="editDocument" autocomplete="off">
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
  							<label class="form-label">RUC</label>
  							<input type="text" class="form-control" name="business_document" id="editBusinessDocument" autocomplete="off">
  						</div>
  					</div>
  					<div class="col-lg-6">
  						<div class="mb-3">
  							<label class="form-label">Razón social</label>
  							<input type="text" class="form-control" name="business_name" id="editBusinessName" autocomplete="off">
  						</div>
  					</div>
  					<div class="col-lg-6">
  						<div class="mb-3">
  							<label class="form-label">Teléfono</label>
  							<input type="text" class="form-control" name="phone" id="editPhone" autocomplete="off">
  						</div>
  					</div>
  					<div class="col-lg-6">
  						<div class="mb-3">
  							<label class="form-label">Correo electrónico</label>
  							<input type="text" class="form-control" name="email" id="editEmail" autocomplete="off">
  						</div>
  					</div>
  					<div class="col-lg-6">
  						<div class="mb-3">
  							<label class="form-label required">Locación</label>
  							<select class="form-select" name="location_id" id="editLocationId">
  								<option value="">Seleccionar</option>
  								@foreach($locations as $location)
  								<option value="{{ $location->id }}">{{ $location->name }}</option>
  								@endforeach
  							</select>
  						</div>
  					</div>
  					<div class="col-lg-6">
  						<div class="mb-3">
  							<label class="form-label required">Tipo de evento</label>
  							<select class="form-select" name="event_type_id" id="editEventTypeId">
  								<option value="">Seleccionar</option>
  								@foreach($event_types as $event_type)
  								<option value="{{ $event_type->id }}">{{ $event_type->name }}</option>
  								@endforeach
  							</select>
  						</div>
  					</div>
  					<div class="col-lg-6">
  						<div class="mb-3">
  							<label class="form-label required">Número de personas</label>
  							<input type="text" class="form-control" name="people_number" id="editPeopleNumber" autocomplete="off">
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
  							<label class="form-label required">Hora de inicio evento</label>
  							<input type="time" class="form-control" name="event_time" id="editEventTime">
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

<div class="modal modal-blur fade" id="extraModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
  	<div class="modal-content">
  		<form id="extraForm" method="POST">
  			<div class="modal-header">
  			  <h5 class="modal-title">Agregar extra</h5>
  			  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
  			</div>
  			<div class="modal-body">
  				<div class="mb-3">
  					<label class="form-label">Extras</label>
  					<table class="table table-bordered">
  						<thead>
  							<tr>
  								<th></th>
  								<th>Nombre</th>
  								<th>Precio</th>
  							</tr>
  						</thead>
  						<tbody>
  							@foreach($extras as $extra)
  							<tr>
  								<td><input type="checkbox" class="form-check-input" name="extra[]" value="{{ $extra->id }}"></td>
  								<td>{{ $extra->name }} </td>
  								<td>{{ $extra->price }}</td>
  							</tr>
  							@endforeach
  						</tbody>
  					</table>
  				</div>
  			</div>
  			<div class="modal-footer">
  				<input type="hidden" id="extraId">
  			  <button type="button" class="btn me-auto" data-bs-dismiss="modal"><i class="ti ti-x icon"></i> Cerrar</button>
  			  <button type="submit" class="btn btn-primary"><i class="ti ti-device-floppy icon"></i> Guardar</button>
  			</div>
  		</form>
    </div>
  </div>
</div>

<div class="modal modal-blur fade" id="employeeModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
  	<div class="modal-content">
  		<form id="employeeForm" method="POST">
  			<div class="modal-header">
  			  <h5 class="modal-title">Agregar personal</h5>
  			  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
  			</div>
  			<div class="modal-body">
  				<div class="mb-3">
  					<label class="form-label">Personal</label>
  					<table class="table table-bordered">
  						<thead>
  							<tr>
  								<th></th>
  								<th>Nombre</th>
  								<th>Puesto</th>
  							</tr>
  						</thead>
  						<tbody>
  							@foreach($employees as $employee)
  							<tr>
  								<td><input type="checkbox" class="form-check-input employee" data-id="{{ $employee->id }}" name="employee[]" value="{{ $employee->id }}"></td>
  								<td>{{ $employee->name }} </td>
  								<td>{{ $employee->job }}</td>
  							</tr>
  							@endforeach
  						</tbody>
  					</table>
  				</div>
  			</div>
  			<div class="modal-footer">
  				<input type="hidden" id="employeeId">
  			  <button type="button" class="btn me-auto" data-bs-dismiss="modal"><i class="ti ti-x icon"></i> Cerrar</button>
  			  <button type="submit" class="btn btn-primary"><i class="ti ti-device-floppy icon"></i> Guardar</button>
  			</div>
  		</form>
    </div>
  </div>
</div>

<div class="modal modal-blur fade" id="scheduleModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered" role="document">
  	<div class="modal-content">
  		<form id="scheduleForm" method="POST">
  			<div class="modal-header">
  			  <h5 class="modal-title">Agregar horario de personal</h5>
  			  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
  			</div>
  			<div class="modal-body">
  				<div class="mb-3">
  					<label class="form-label">Fecha de evento: <span id="lbl-date"></span></label>
  				</div>
  				<div class="mb-3">
  					<label class="form-label">Personal</label>
  					<table class="table table-bordered">
  						<thead>
  							<tr>
  								<th>Nombre</th>
  								<th>Puesto</th>
  								<th>H. Entrada</th>
  								<th>H. Salida</th>
  							</tr>
  						</thead>
  						<tbody id="tbl-employees">	
  						</tbody>
  					</table>
  				</div>
  			</div>
  			<div class="modal-footer">
  				<input type="hidden" id="scheduleId">
  			  <button type="button" class="btn me-auto" data-bs-dismiss="modal"><i class="ti ti-x icon"></i> Cerrar</button>
  			  <button type="submit" class="btn btn-primary"><i class="ti ti-device-floppy icon"></i> Guardar</button>
  			</div>
  		</form>
    </div>
  </div>
</div>

<div class="modal modal-blur fade" id="totalModal" tabindex="-1" role="dialog" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-sm" role="document">
  	<div class="modal-content">
  		<form id="totalForm" method="POST">
  			<div class="modal-header">
  			  <h5 class="modal-title">Editar total</h5>
  			  <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
  			</div>
  			<div class="modal-body">
  				<div class="mb-3">
  					<label class="form-label required">Total</label>
  					<input type="text" class="form-control" name="total" id="total" autocomplete="off">
  				</div>
  			</div>
  			<div class="modal-footer">
  				<input type="hidden" id="totalId">
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

	$(document).ready(function(){
		var queryString = window.location.search;
		var parametros = new URLSearchParams(queryString);

		if(parametros.get('modal') == 'create'){
			$('#createModal').modal('show');
		}
	});

	$('#storeForm').submit(function(e){
		e.preventDefault();

		$.ajax({
			url: '{{ route('contracts.store') }}',
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
			url: '{{ route('contracts.index') }}' + '/' + id + '/edit/',
			method: 'GET',
			success: function(data){
				$('#editDocument').val(data.document);
				$('#editName').val(data.name);
				$('#editBusinessDocument').val(data.business_document);
				$('#editBusinessName').val(data.business_name);
				$('#editPhone').val(data.phone);
				$('#editEmail').val(data.email);
				$('#editLocationId').val(data.location_id);
				$('#editEventTypeId').val(data.event_type_id);
				$('#editPeopleNumber').val(data.people_number);
				$('#editEventDate').val(data.event_date);
				$('#editEventTime').val(data.event_time);
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
			url: '{{ route('contracts.index') }}' + '/' + id + '',
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

	$(document).on('click', '.btn-extra', function(){

		var id = $(this).data('id');

		$('#extraId').val(id);
		$('#extraModal').modal('show');

	});

	$('#extraForm').submit(function(e){
		e.preventDefault();

		var id = $('#extraId').val();

		$.ajax({
			url: '{{ route('contracts.index') }}' + '/' + id + '/extra',
			method: 'POST',
			data: $(this).serialize(),
			success: function(data){
				if(data.status){
					$('#extraModal').modal('hide');
					$('#extraForm')[0].reset();
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

	$(document).on('click', '.btn-employee', function(){

		var id = $(this).data('id');

		$('.employee').each(function(){
			$(this).attr('checked', false);
		});

		$.ajax({
			url: '{{ route('contracts.index') }}' + '/' + id + '/employees',
			method: 'GET',
			success: function(data){
				$('.employee').each(function(){

					if( data.indexOf($(this).data('id')) != -1 ){
						$(this).attr('checked', true)
					}

				});
			}
		});

		$('#employeeId').val(id);
		$('#employeeModal').modal('show');

	});

	$('#employeeForm').submit(function(e){
		e.preventDefault();

		var id = $('#employeeId').val();

		$.ajax({
			url: '{{ route('contracts.index') }}' + '/' + id + '/employee',
			method: 'POST',
			data: $(this).serialize(),
			success: function(data){
				if(data.status){
					$('#employeeModal').modal('hide');
					$('#employeeForm')[0].reset();
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

	$(document).on('click', '.btn-schedule', function(){

		var id = $(this).data('id');

		$('#lbl-date').text('');

		$.ajax({
			url: '{{ route('contracts.index') }}' + '/' + id + '/schedules',
			method: 'GET',
			success: function(data){
				var html = '';

				data.employees.forEach(function(employee){
					html += `
						<tr>
							<td>${employee.name}</td>
							<td>${employee.job}</td>
							<td><input type="text" class="form-control" name="start[]" value="${employee.start}"></td>
							<td><input type="text" class="form-control" name="end[]" value="${employee.end}"></td>
						</tr>
					`;
				});

				$('#lbl-date').text(data.date);
				$('#tbl-employees').html(html);

			}
		});

		$('#scheduleId').val(id);
		$('#scheduleModal').modal('show');

	});

	$('#scheduleForm').submit(function(e){
		e.preventDefault();

		var id = $('#scheduleId').val();

		$.ajax({
			url: '{{ route('contracts.index') }}' + '/' + id + '/schedule',
			method: 'POST',
			data: $(this).serialize(),
			success: function(data){
				if(data.status){
					$('#scheduleModal').modal('hide');
					$('#scheduleForm')[0].reset();
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
			text: 'Para eliminar el contrato, ingrese el PIN de 4 digitos',
			input: 'password',
			inputValidator: (value) => {
        if (value.length != 4) {
          return 'El PIN es requerido y debe tener 4 digitos';
        }
      }
		}).then((result) => {
			
			if(result.isConfirmed){

				var pin = result.value;

				$.ajax({
					url: '{{ route('contracts.index') }}' + '/' + id,
					method: 'DELETE',
					data: {pin:pin},
					success: function(data){
						if(data.status){
							ToastMessage.fire({ text: 'Registro eliminado' })
								.then(() => location.reload());
						}else{
							ToastError.fire({ text: data.error ? data.error : 'Ocurrió un error' });
						}
						
					},
					error: function(err){
						ToastError.fire({ text: 'Ocurrió un error' });
					}
				});
				
			}

		});
	});

	$(document).on('click', '.btn-total', function(){

		var id = $(this).data('id');

		$.ajax({
			url: '{{ route('contracts.index') }}' + '/' + id + '/total',
			method: 'GET',
			success: function(data){
				$('#total').val(data.total);
				$('#totalId').val(data.id);
				$('#totalModal').modal('show');
			},
			error: function(err){
				ToastError.fire({ text: 'Ocurrió un error' });
			}
		});

	});

	$('#totalForm').submit(function(e){
		e.preventDefault();

		var id = $('#totalId').val();

		$.ajax({
			url: '{{ route('contracts.index') }}' + '/' + id + '/total',
			method: 'POST',
			data: $(this).serialize(),
			success: function(data){
				if(data.status){
					$('#totalModal').modal('hide');
					$('#totalForm')[0].reset();
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



</script>
@endsection