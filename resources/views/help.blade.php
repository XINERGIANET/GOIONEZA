@extends('template.app')

@section('title', 'Ayuda')

@section('content')
<div class="row">
	<div class="col-md-4">
		<div class="card">
			<div class="card-body">
				<h5 class="card-title">
					Atención al cliente
				</h5>
				<span class="fw-bold fs-2">944031514 - 940174022</span>
			</div>
		</div>
	</div>
	<div class="col-md-4">
		<div class="card">
			<div class="card-body">
				<h5 class="card-title">
					Correo de contacto
				</h5>
				<span class="fw-bold fs-2">contacto@xpandecorp.com</span>
			</div>
		</div>
	</div>
	<div class="col-md-4">
		<div class="card">
			<div class="card-body">
				<h5 class="card-title">
					WhatsApp
				</h5>
				<a href="{{ url('wa.me/51944031514') }}" class="btn btn-success" target="_blank">WhatsApp</a>
			</div>
		</div>
	</div>
</div>

@endsection