@extends('template.app')

@section('title', 'Ajustes')

@section('content')
<div class="row">
	<div class="col-md-8">
		<div class="card mb-4">
			<div class="card-header">
				<h4 class="card-title">Cambiar contraseña</h4>
			</div>
			<div class="card-body">
				<form method="POST" action="{{ route('settings.password') }}">
					@csrf
					<div class="row">
						<div class="col-lg-6">
							<div class="mb-3">
								<label class="form-label">Contraseña actual</label>
								<input type="password" class="form-control @error('password') is-invalid @enderror" name="password">
								@error('password')
								<div class="invalid-feedback">{{ $message }}</div>
								@enderror
							</div>
						</div>
						<div class="col-lg-6">
							<div class="mb-3">
								<label class="form-label">Nueva contraseña</label>
								<input type="password" class="form-control @error('new_password') is-invalid @enderror" name="new_password">
								@error('new_password')
								<div class="invalid-feedback">{{ $message }}</div>
								@enderror
							</div>
						</div>
					</div>
					<button class="btn btn-primary" id="btn-save">
						<i class="ti ti-device-floppy icon"></i> Guardar
					</button>
				</form>
			</div>
		</div>
	</div>
	<div class="col-md-4">
		<div class="card mb-4">
			<div class="card-header">
				<h4 class="card-title">Cambiar PIN de eliminación</h4>
			</div>
			<div class="card-body">
				<form method="POST" action="{{ route('settings.pin') }}">
					@csrf
					<div class="mb-3">
						<label class="form-label">PIN</label>
						<input type="text" class="form-control @error('pin') is-invalid @enderror" name="pin" value="{{ $pin }}">
						@error('pin')
						<div class="invalid-feedback">{{ $message }}</div>
						@enderror
					</div>
					<button class="btn btn-primary" id="btn-save">
						<i class="ti ti-device-floppy icon"></i> Guardar
					</button>
				</form>
			</div>
		</div>
	</div>
</div>

@endsection