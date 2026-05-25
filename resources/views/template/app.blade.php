<!DOCTYPE html>
<html lang="es">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta name="csrf-token" content="{{ csrf_token() }}" />
	<title>Quinta Fernandini</title>
	<link rel="stylesheet" href="{{ asset('assets/css/tabler.min.css') }}">
	<link rel="stylesheet" href="{{ asset('assets/css/tabler-vendors.min.css') }}">
	<link rel="stylesheet" href="{{ asset('assets/css/tabler-icons.min.css') }}">
	<link rel="stylesheet" href="{{ asset('assets/css/sweetalert2-theme-material-ui.css') }}">
	<link rel="stylesheet" href="{{ asset('assets/css/styles.css') }}">
	<link rel="icon" href="{{ asset('assets/images/xinergia-icon.svg') }}">
	@yield('styles')
</head>
<body>
	<div class="page">
		<aside class="navbar navbar-vertical navbar-expand-lg" data-bs-theme="dark">
			<div class="container-fluid">
				<button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#sidebar-menu" aria-controls="sidebar-menu" aria-expanded="false" aria-label="Toggle navigation">
					<span class="navbar-toggler-icon"></span>
				</button>
				<h1 class="navbar-brand navbar-brand-autodark">
					<a href=".">
						Quinta Fernandini
					</a>
				</h1>
				<div class="navbar-nav flex-row d-lg-none">
					<div class="nav-item dropdown">
						<a href="#" class="nav-link d-flex lh-1 text-reset p-0" data-bs-toggle="dropdown" aria-label="Open user menu">
							<span class="avatar avatar-sm text-white">
								{{-- <i class="ti ti-user icon"></i> --}}
								<img src="{{ asset('assets/images/avatar.webp') }}">
							</span>
							<div class="d-none d-xl-block ps-2">
								<div>{{ auth()->user()->name }}</div>
								<div class="mt-1 small text-muted">{{ auth()->user()->user }}</div>
							</div>
						</a>
						<div class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
							<a href="{{ route('settings.index') }}" class="dropdown-item">Ajustes</a>
							<form method="POST" action="{{ route('auth.logout') }}">
								@csrf
								<a href="javascript:void(0)" class="dropdown-item" onclick="this.closest('form').submit()">Cerrar sesión</a>
							</form>
						</div>
					</div>
				</div>
				<div class="collapse navbar-collapse" id="sidebar-menu">
					<ul class="navbar-nav pt-lg-3">
						<li class="nav-item">
							<a class="nav-link" href="{{ url('/') }}" >
								<span class="nav-link-icon d-md-none d-lg-inline-block">
									<i class="ti ti-home icon"></i>
								</span>
								<span class="nav-link-title">
									Inicio
								</span>
							</a>
						</li>
						@if(auth()->user()->user == 'admin')
						<li class="nav-item">
							<a class="nav-link" href="{{ route('calendar') }}" >
								<span class="nav-link-icon d-md-none d-lg-inline-block">
									<i class="ti ti-calendar icon"></i>
								</span>
								<span class="nav-link-title">
									Calendario
								</span>
							</a>
						</li>
						<li class="nav-item dropdown">
							<a class="nav-link dropdown-toggle" href="#navbar-register" data-bs-toggle="dropdown" data-bs-auto-close="false" role="button" aria-expanded="true" >
								<span class="nav-link-icon d-md-none d-lg-inline-block">
									<i class="ti ti-edit icon"></i>
								</span>
								<span class="nav-link-title">
									Registro
								</span>
							</a>
							<div class="dropdown-menu">
								<div class="dropdown-menu-columns">
									<div class="dropdown-menu-column">
										<a class="dropdown-item" href="{{ route('locations.index') }}">
											Locaciones
										</a>
										<a class="dropdown-item" href="{{ route('event_types.index') }}">
											Tipos de evento
										</a>
										<a class="dropdown-item" href="{{ route('packages.index') }}">
											Paquetes
										</a>
										<a class="dropdown-item" href="{{ route('extras.index') }}">
											Extras
										</a>
										<a class="dropdown-item" href="{{ route('clients.index') }}">
											Clientes
										</a>
										<a class="dropdown-item" href="{{ route('providers.index') }}">
											Proveedores
										</a>
										<a class="dropdown-item" href="{{ route('employees.index') }}">
											Personal
										</a>
										<a class="dropdown-item" href="{{ route('income_types.index') }}">
											Ingreso (categorías)
										</a>
										<a class="dropdown-item" href="{{ route('expense_types.index') }}">
											Egreso (categorías)
										</a>
										<a class="dropdown-item" href="{{ route('product_types.index') }}">
											Almacen (categorías)
										</a>
										<a class="dropdown-item" href="{{ route('payment_schedules.index') }}">
											Cronograma de pagos
										</a>
									</div>
								</div>
							</div>
						</li>
						@endif

						@if(auth()->user()->user == 'ventas' || auth()->user()->user == 'admin')
						<li class="nav-item">
							<a class="nav-link" href="{{ route('quotations.index') }}" >
								<span class="nav-link-icon d-md-none d-lg-inline-block">
									<i class="ti ti-file-invoice icon"></i>
								</span>
								<span class="nav-link-title">
									Cotizaciones
								</span>
							</a>
						</li>
						<li class="nav-item">
							<a class="nav-link" href="{{ route('contracts.index') }}" >
								<span class="nav-link-icon d-md-none d-lg-inline-block">
									<i class="ti ti-cash icon"></i>
								</span>
								<span class="nav-link-title">
									Contratos
								</span>
							</a>
						</li>
						@endif

						<li class="nav-item dropdown">
							<a class="nav-link dropdown-toggle" href="#navbar-register" data-bs-toggle="dropdown" data-bs-auto-close="false" role="button" aria-expanded="true" >
								<span class="nav-link-icon d-md-none d-lg-inline-block">
									<i class="ti ti-edit icon"></i>
								</span>
								<span class="nav-link-title">
									Finanzas
								</span>
							</a>
							<div class="dropdown-menu">
								<div class="dropdown-menu-columns">
									<div class="dropdown-menu-column">

										@if(auth()->user()->user == 'compras' || auth()->user()->user == 'admin')
										<a class="dropdown-item" href="{{ route('purchases.index') }}">
											Egresos generales
										</a>
										<a class="dropdown-item" href="{{ route('expenses.index') }}">
											Gastos por evento
										</a>

										@if(auth()->user()->user == 'admin')
										<a class="dropdown-item" href="{{ route('incomes.index') }}">
											Otros ingresos
										</a>
										<a class="dropdown-item" href="{{ route('contracts.charges') }}">
											Cuentas por cobrar
										</a>
										<a class="dropdown-item" href="{{ route('payment_schedules.index') }}">
											Cuentas por pagar
										</a>
										<a class="dropdown-item" href="{{ route('cash_flow') }}">
											Flujo de caja
										</a>
										@endif
										@endif

									</div>
								</div>
							</div>
						</li>

						@if(auth()->user()->user == 'almacen' || auth()->user()->user == 'admin')
						<li class="nav-item">
							<a class="nav-link" href="{{ route('products.index') }}" >
								<span class="nav-link-icon d-md-none d-lg-inline-block">
									<i class="ti ti-box icon"></i>
								</span>
								<span class="nav-link-title">
									Almacen
								</span>
							</a>
						</li>
						@endif

						<li class="nav-item">
							<a class="nav-link" href="{{ url('/') }}" >
								<span class="nav-link-icon d-md-none d-lg-inline-block">
									<i class="ti ti-help icon"></i>
								</span>
								<span class="nav-link-title">
									Ayuda
								</span>
							</a>
						</li>
					</ul>
				</div>
			</div>
		</aside>
		<header class="navbar navbar-expand-md d-none d-lg-flex d-print-none">
			<div class="container-xl">
				<button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbar-menu" aria-controls="navbar-menu" aria-expanded="false" aria-label="Toggle navigation">
					<span class="navbar-toggler-icon"></span>
				</button>
				<div class="navbar-nav flex-row order-md-last">
					<div class="d-none d-md-flex">
						<a href="?theme=dark" class="nav-link px-0 hide-theme-dark" title="Activar modo oscuro" data-bs-toggle="tooltip" data-bs-placement="bottom">
							<i class="ti ti-moon icon"></i>
						</a>
						<a href="?theme=light" class="nav-link px-0 hide-theme-light" title="Activar modo claro" data-bs-toggle="tooltip" data-bs-placement="bottom">
							<i class="ti ti-sun icon"></i>
						</a>
						<div class="nav-item dropdown d-none d-md-flex me-3">
							@php
							$event_notifications = App\Models\Contract::active()->whereDate('event_date', now())->count();
							$charge_notifications = App\Models\Contract::active()->whereDate('debt_payment_date', now())->where('paid', 0)->count();
							$pay_notifications = App\Models\PaymentSchedule::active()->where('day', now()->format('d'))->count();
							$notifications = $event_notifications + $charge_notifications + $pay_notifications;
							@endphp
							<a href="#" class="nav-link px-0" data-bs-toggle="dropdown" tabindex="-1" aria-label="Show Notifications">
								<i class="ti ti-bell icon"></i>
								@if($notifications > 0)
								<span class="badge bg-red"></span>
								@endif
							</a>
							<div class="dropdown-menu dropdown-menu-arrow dropdown-menu-end dropdown-menu-card">
								<div class="card">
									<div class="card-header">
										<h3 class="card-title">Notificaciones de hoy</h3>
									</div>
									<div class="list-group list-group-flush list-group-hoverable">

										@if($event_notifications > 0)
										<div class="list-group-item">
											<div class="row align-items-center">
												<div class="col text-truncate">
													<div class="d-block text-truncate mt-n1">
													Tienes <b>{{ $event_notifications }}</b> evento(s). <a href="{{ route('calendar') }}">Ir a calendario</a>
													</div>
												</div>
											</div>
										</div>
										@endif

										@if($charge_notifications)
										<div class="list-group-item">
											<div class="row align-items-center">
												<div class="col text-truncate">
													<div class="d-block text-truncate mt-n1">
													Tienes <b>{{ $charge_notifications }}</b> cuenta(s) por cobrar. <a href="{{ route('contracts.charges') }}">Ir a cobranzas</a>
													</div>
												</div>
											</div>
										</div>
										@endif

										@if($pay_notifications)
										<div class="list-group-item">
											<div class="row align-items-center">
												<div class="col text-truncate">
													<div class="d-block text-truncate mt-n1">
													Tienes <b>{{ $pay_notifications }}</b> cuenta(s) por pagar. <a href="{{ route('payment_schedules.index') }}">Ir a cronograma de pagos</a>
													</div>
												</div>
											</div>
										</div>
										@endif

										@if($notifications == 0)
										<div class="list-group-item">
											<div class="row align-items-center">
												<div class="col text-truncate">
													<div class="d-block text-truncate">
													No tienes más notificaciones.
													</div>
												</div>
											</div>
										</div>
										@endif
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="nav-item dropdown">
						<a href="#" class="nav-link d-flex lh-1 text-reset p-0" data-bs-toggle="dropdown" aria-label="Open user menu">
							<span class="avatar avatar-sm">
								{{-- <i class="ti ti-user icon"></i> --}}
								<img src="{{ asset('assets/images/avatar.webp') }}">
							</span>
							<div class="d-none d-xl-block ps-2">
								<div>{{ auth()->user()->name }}</div>
								<div class="mt-1 small text-muted">{{ auth()->user()->user }}</div>
							</div>
						</a>
						<div class="dropdown-menu dropdown-menu-end dropdown-menu-arrow">
							<a href="{{ route('settings.index') }}" class="dropdown-item">Ajustes</a>
							<form method="POST" action="{{ route('auth.logout') }}">
								@csrf
								<a href="javascript:void(0)" class="dropdown-item" onclick="this.closest('form').submit()">Cerrar sesión</a>
							</form>
						</div>
					</div>
				</div>
				<div class="collapse navbar-collapse" id="navbar-menu">
				    {{-- <div>
				      <form action="" method="get" autocomplete="off" novalidate>
				        <div class="input-icon">
				          <span class="input-icon-addon">
				            <i class="ti ti-search icon"></i>
				          </span>
				          <input type="text" value="" class="form-control" placeholder="Buscar" aria-label="Search in website">
				        </div>
				      </form>
				  </div> --}}
				</div>
			</div>
		</header>
		<div class="page-wrapper">
			<!-- Page header -->
			<div class="page-header d-print-none">
				<div class="container-xl">
					<div class="row g-2 align-items-center">
						<div class="col">
							<h2 class="page-title">
								@yield('title')
							</h2>
						</div>
					</div>
				</div>
			</div>
			<!-- Page body -->
			<div class="page-body">
				<div class="container-xl">
					@if(session()->has('message'))
					<div class="alert alert-success">
						{{ session()->get('message') }}
					</div>
					@endif
					@if(session()->has('error'))
					<div class="alert alert-danger">
						{{ session()->get('error') }}
					</div>
					@endif
					@yield('content')
				</div>
			</div>
			<footer class="footer footer-transparent d-print-none">
				<div class="container-xl">
					<div class="row text-center align-items-center flex-row-reverse">
						<div class="col-lg-auto ms-lg-auto">
						</div>
						<div class="col-12 col-lg-auto mt-3 mt-lg-0">
							<ul class="list-inline list-inline-dots mb-0">
								<li class="list-inline-item">
									Copyright &copy; 2023
									<a href="/" class="link-secondary">Xinergia</a>
								</li>
							</ul>
						</div>
					</div>
				</div>
			</footer>
		</div>
	</div>

<script src="{{ asset('assets/js/tabler.min.js') }}"></script>
<script src="{{ asset('assets/js/theme.min.js') }}"></script>
<script src="{{ asset('assets/js/tom-select.base.min.js') }}"></script>
<script src="{{ asset('assets/js/sweetalert2.min.js') }}"></script>
<script src="https://code.jquery.com/jquery-3.6.3.min.js"></script>
<script>
	$.ajaxSetup({
		headers: {
			'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
		}
	});

	const ToastError = Swal.mixin({
		title: 'Error',
		icon: 'error',
		toast: true,
		position: 'bottom-end',
		timer: 2000,
		timerProgressBar: true
	});

	const ToastMessage = Swal.mixin({
		title: 'Mensaje',
		icon: 'success',
		toast: true,
		position: 'bottom-end',
		timer: 2000,
		timerProgressBar: true
	});

	const ToastConfirm = Swal.mixin({
		icon: 'question',
		showDenyButton: true,
		confirmButtonText: 'Aceptar',
		denyButtonText: 'Cancelar',
		toast: true,
		position: 'bottom-end'
	});
</script>
@yield('scripts')
</body>
</html>