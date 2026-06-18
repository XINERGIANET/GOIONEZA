@extends('template.app')

@section('title', 'Calendario')

@section('content')
<div class="card">
	<div class="card-header border-bottom">
		<div class="d-flex align-items-center gap-3">
			<span class="d-flex align-items-center gap-2"><span class="badge" style="width: 15px; height: 15px; background-color: #28a745;"></span> Contratos</span>
			<span class="d-flex align-items-center gap-2"><span class="badge" style="width: 15px; height: 15px; background-color: #ffc107;"></span> Visitas de Cotización</span>
		</div>
	</div>
	<div class="card-body">
		<div id="calendar"></div>
	</div>
</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>
<script>
	document.addEventListener('DOMContentLoaded', function() {
		var calendarEl = document.getElementById('calendar');
		
		var calendar = new FullCalendar.Calendar(calendarEl, {
			buttonText: {
				'today': 'hoy'
			},
			locale: 'es',
			initialView: 'dayGridMonth',
			events: '{{ route('events') }}',
			eventClick: function(info){
				var event = info.event;
				var html = '';
				
				if(event.extendedProps.is_quotation) {
					html = `
						<div class="text-start">
							<p><b>Cliente:</b> ${event.extendedProps.name}</p>
							<p><b>Teléfono:</b> ${event.extendedProps.phone}</p>
							<p><b>Paquete:</b> ${event.extendedProps.package}</p>
							<p><b>Número de personas:</b> ${event.extendedProps.people_number}</p>
							<p><b>Fecha de evento esperada:</b> ${event.extendedProps.event_date}</p>
							<p><b>Fecha de visita:</b> ${event.extendedProps.visit_date}</p>
						</div>
					`;
				} else {
					html = `
						<div class="text-start">
							<p><b>Cliente:</b> ${event.extendedProps.name}</p>
							<p><b>Paquete:</b> ${event.extendedProps.package}</p>
							<p><b>Número de personas:</b> ${event.extendedProps.people_number}</p>
							<p><b>Fecha de evento:</b> ${event.extendedProps.event_date}</p>
							<p><b>Hora de evento:</b> ${event.extendedProps.event_time}</p>
							<p><b>Tipo de evento:</b> ${event.extendedProps.event_type}</p>
							<p><b>Locación:</b> ${event.extendedProps.location}</p>
						</div>
					`;
				}

				Swal.fire({
					'icon': 'info',
					'title': event.title,
					'html': html
				});
				console.log(event);
			}
		});

		calendar.render();
	});
</script>
@endsection