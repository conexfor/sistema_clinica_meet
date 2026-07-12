<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AdminReportController;
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\ListaEsperaController;
use App\Http\Controllers\SoporteController;
use App\Http\Controllers\UsuarioController;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\CustomerPortalController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DoctorController;
use App\Http\Controllers\ReceptionistController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Auth\GoogleAuthController;
use App\Http\Controllers\MeetingController;

Route::get('/', [DashboardController::class, 'index'])->name('landing');

Route::get('/inicio', function () {
    return view('dashboard');
})->name('inicio');

// Authentication Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    
    Route::get('/register', [AuthController::class, 'showRegisterForm'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Soporte: reportar incidencia (cualquier usuario autenticado)
Route::post('/soporte', [SoporteController::class, 'store'])->middleware('auth')->name('soporte.store');

Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

// Booking Flow (Protected by auth)
Route::middleware('auth')->group(function () {
    Route::get('/booking/service', [BookingController::class, 'selectService'])->name('booking.service');
    Route::get('/booking/specialist', [BookingController::class, 'selectSpecialist'])->name('booking.specialist');
    Route::get('/booking/schedule', [BookingController::class, 'schedule'])->name('booking.schedule');
    Route::get('/booking/register', [BookingController::class, 'register'])->name('booking.register');
    Route::get('/booking/checkout', [BookingController::class, 'checkout'])->name('booking.checkout');
    Route::post('/booking/store', [BookingController::class, 'store'])->name('booking.store');
    Route::get('/booking/reschedule/{cita}', [BookingController::class, 'reschedule'])->name('booking.reschedule');
    Route::get('/booking/join-meeting/{cita}', [BookingController::class, 'joinMeeting'])->name('booking.join_meeting');
    Route::post('/booking/lista-espera', [BookingController::class, 'joinWaitlist'])->name('booking.waitlist');
});

// Patient Portal
Route::group(['middleware' => ['auth', 'role:patient'], 'prefix' => 'portal', 'as' => 'portal.'], function () {
    Route::get('/', [CustomerPortalController::class, 'index'])->name('index');
    Route::get('/historial', [CustomerPortalController::class, 'history'])->name('history');
    Route::get('/soporte', [CustomerPortalController::class, 'soporte'])->name('soporte');
    Route::get('/perfil', [CustomerPortalController::class, 'profile'])->name('profile');
    Route::put('/perfil', [CustomerPortalController::class, 'updateProfile'])->name('profile.update');
    Route::post('/citas/{cita}/calificar', [CustomerPortalController::class, 'rateAppointment'])->name('appointments.rate');

    // Tareas asignadas por el especialista
    Route::patch('/tareas/{tarea}/toggle', [CustomerPortalController::class, 'toggleTask'])->name('tasks.toggle');

    // Registro de ánimo por sesión (inicio/fin)
    Route::patch('/citas/{cita}/animo', [CustomerPortalController::class, 'savePatientAnimo'])->name('citas.animo');

    // Cancelar cita (por el paciente)
    Route::patch('/citas/{cita}/cancelar', [CustomerPortalController::class, 'cancelAppointment'])->name('citas.cancel');

    // Notificaciones
    Route::patch('/notificaciones/leer', [CustomerPortalController::class, 'marcarNotificacionesLeidas'])->name('notificaciones.leer');

    // Solicitud de reprogramación
    Route::get('/reprogramar/{cita}', [CustomerPortalController::class, 'rescheduleCreate'])->name('reschedule.create');
    Route::post('/reprogramar/{cita}', [CustomerPortalController::class, 'rescheduleStore'])->name('reschedule.store');
});

// Admin Routes
Route::middleware(['auth', 'role:admin'])->prefix('admin')->as('admin.')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');

    // Notificaciones
    Route::patch('/notificaciones/leer', [AdminController::class, 'marcarNotificacionesLeidas'])->name('notificaciones.leer');

    // Gestión de Especialistas
    Route::get('/especialistas', [AdminController::class, 'especialistasIndex'])->name('especialistas.index');
    Route::get('/especialistas/create', [AdminController::class, 'createEspecialista'])->name('especialistas.create');
    Route::post('/especialistas', [AdminController::class, 'storeEspecialista'])->name('especialistas.store');
    Route::get('/especialistas/{id}/edit', [AdminController::class, 'editEspecialista'])->name('especialistas.edit');
    Route::put('/especialistas/{id}', [AdminController::class, 'updateEspecialista'])->name('especialistas.update');

    // Gestión de Servicios
    Route::get('/servicios', [AdminController::class, 'serviciosIndex'])->name('servicios.index');
    Route::get('/servicios/crear', [AdminController::class, 'serviciosCreate'])->name('servicios.create');
    Route::post('/servicios', [AdminController::class, 'serviciosStore'])->name('servicios.store');
    Route::get('/servicios/{id}/editar', [AdminController::class, 'serviciosEdit'])->name('servicios.edit');
    Route::put('/servicios/{id}', [AdminController::class, 'serviciosUpdate'])->name('servicios.update');
    Route::patch('/servicios/{id}/toggle', [AdminController::class, 'serviciosToggleStatus'])->name('servicios.toggle');
    Route::delete('/servicios/{id}', [AdminController::class, 'serviciosDestroy'])->name('servicios.destroy');

    // Gestión de Categorías
    Route::get('/categorias', [CategoriaController::class, 'index'])->name('categorias.index');
    Route::get('/categorias/crear', [CategoriaController::class, 'create'])->name('categorias.create');
    Route::post('/categorias', [CategoriaController::class, 'store'])->name('categorias.store');
    Route::get('/categorias/{id}/editar', [CategoriaController::class, 'edit'])->name('categorias.edit');
    Route::put('/categorias/{id}', [CategoriaController::class, 'update'])->name('categorias.update');
    Route::patch('/categorias/{id}/toggle', [CategoriaController::class, 'toggle'])->name('categorias.toggle');
    Route::delete('/categorias/{id}', [CategoriaController::class, 'destroy'])->name('categorias.destroy');

    // Reportes y Analíticas (sub-páginas)
    Route::get('/reportes/finanzas', [AdminReportController::class, 'finanzas'])->name('reportes.finanzas');
    Route::get('/reportes/desempeno', [AdminReportController::class, 'desempeno'])->name('reportes.desempeno');
    Route::get('/reportes/ocupacion', [AdminReportController::class, 'ocupacion'])->name('reportes.ocupacion');
    Route::get('/reportes/control', [AdminReportController::class, 'control'])->name('reportes.control');
    Route::get('/reportes/auditoria', [AdminReportController::class, 'auditoria'])->name('reportes.auditoria');
    Route::get('/reportes/drill/{metric}', [AdminReportController::class, 'drill'])->name('reportes.drill');

    // Lista de espera
    Route::get('/lista-espera', [ListaEsperaController::class, 'index'])->name('lista-espera.index');
    Route::post('/lista-espera', [ListaEsperaController::class, 'store'])->name('lista-espera.store');
    Route::patch('/lista-espera/{id}/estado', [ListaEsperaController::class, 'updateEstado'])->name('lista-espera.estado');
    Route::delete('/lista-espera/{id}', [ListaEsperaController::class, 'destroy'])->name('lista-espera.destroy');

    // Bandeja de soporte
    Route::get('/soporte', [SoporteController::class, 'index'])->name('soporte.index');
    Route::post('/soporte/{id}/responder', [SoporteController::class, 'respond'])->name('soporte.respond');
    Route::patch('/soporte/{id}/estado', [SoporteController::class, 'updateEstado'])->name('soporte.estado');

    // Gestión de Usuarios (todas las cuentas)
    Route::get('/usuarios', [UsuarioController::class, 'index'])->name('usuarios.index');
    Route::patch('/usuarios/{id}/estado', [UsuarioController::class, 'toggleEstado'])->name('usuarios.estado');

    // Reportes y Listados
    Route::get('/pacientes', [AdminController::class, 'pacientesIndex'])->name('pacientes.index');
    Route::get('/citas', [AdminController::class, 'citasIndex'])->name('citas.index');
});

// Psychologist Routes
Route::middleware(['auth', 'role:doctor'])->prefix('doctor')->as('doctor.')->group(function () {
    Route::get('/dashboard', [DoctorController::class, 'dashboard'])->name('dashboard');
    Route::get('/metricas', [DoctorController::class, 'metrics'])->name('metrics');
    Route::post('/recordatorios/enviar', [DoctorController::class, 'enviarRecordatorios'])->name('recordatorios.enviar');
    Route::get('/schedule', [DoctorController::class, 'schedule'])->name('schedule');
    Route::post('/schedule', [DoctorController::class, 'storeSchedule'])->name('schedule.store');
    Route::delete('/schedule/{id}', [DoctorController::class, 'deleteSchedule'])->name('schedule.destroy');
    
    // Horarios Semanales
    Route::post('/weekly-schedule', [DoctorController::class, 'storeWeeklySchedule'])->name('weekly.store');
    Route::delete('/weekly-schedule/{id}', [DoctorController::class, 'deleteWeeklySchedule'])->name('weekly.destroy');
    
    // Servicios
    Route::get('/services', [DoctorController::class, 'services'])->name('services');
    Route::post('/services', [DoctorController::class, 'updateServices'])->name('update_services');

    Route::get('/patients', [DoctorController::class, 'patients'])->name('patients.index');
    Route::get('/patients/{id}', [DoctorController::class, 'showPatient'])->name('patients.show');
    Route::get('/join-meeting/{cita}', [DoctorController::class, 'joinMeeting'])->name('join_meeting');
    Route::patch('/appointments/{cita}/status', [DoctorController::class, 'updateStatus'])->name('appointments.update_status');

    // Espacio de sesión: tareas del paciente y resumen de la sesión
    Route::get('/sesion/{cita}', [DoctorController::class, 'sessionWorkspace'])->name('session');
    Route::post('/sesion/{cita}/tareas', [DoctorController::class, 'storeTask'])->name('tasks.store');
    Route::delete('/tareas/{tarea}', [DoctorController::class, 'deleteTask'])->name('tasks.destroy');
    Route::put('/sesion/{cita}/resumen', [DoctorController::class, 'saveResumen'])->name('session.resumen');
    Route::patch('/sesion/{cita}/animo', [DoctorController::class, 'saveAnimo'])->name('session.animo');

    // Ficha del paciente: notas clínicas y reporte de crisis
    Route::post('/patients/{id}/notas', [DoctorController::class, 'storePatientNote'])->name('patients.notes.store');
    Route::post('/patients/{id}/crisis', [DoctorController::class, 'reportCrisis'])->name('patients.crisis');
    Route::delete('/notas/{nota}', [DoctorController::class, 'deleteNota'])->name('patients.notes.destroy');

    // Solicitudes de reprogramación
    Route::post('/solicitudes/{solicitud}/aprobar', [DoctorController::class, 'approveSolicitud'])->name('solicitudes.approve');
    Route::post('/solicitudes/{solicitud}/rechazar', [DoctorController::class, 'rejectSolicitud'])->name('solicitudes.reject');

    // Notificaciones del doctor
    Route::patch('/notificaciones/leer', [DoctorController::class, 'marcarNotificacionesLeidas'])->name('notificaciones.leer');

    // Notas del Psicólogo
    Route::post('/notes', [App\Http\Controllers\NotaController::class, 'store'])->name('notes.store');
    Route::put('/notes/{id}', [App\Http\Controllers\NotaController::class, 'update'])->name('notes.update');
    Route::delete('/notes/{id}', [App\Http\Controllers\NotaController::class, 'destroy'])->name('notes.destroy');

    // Perfil
    Route::get('/profile', [DoctorController::class, 'profile'])->name('profile');
    Route::put('/profile', [DoctorController::class, 'updateProfile'])->name('profile.update');
});

// Receptionist Routes
Route::group(['middleware' => ['auth', 'role:receptionist'], 'prefix' => 'receptionist', 'as' => 'receptionist.'], function () {
    Route::get('/dashboard', [ReceptionistController::class, 'dashboard'])->name('dashboard');
    Route::get('/booking/create', [ReceptionistController::class, 'createBooking'])->name('booking.create');
    Route::post('/booking/store', [ReceptionistController::class, 'storeBooking'])->name('booking.store');
    Route::get('/patients/search', [ReceptionistController::class, 'searchPatients'])->name('patients.search');
    Route::get('/api/availability/search', [ReceptionistController::class, 'getAvailability']);
    Route::get('/api/availability/dates', [ReceptionistController::class, 'getAvailableDates'])->name('availability.dates');
    Route::get('/api/especialistas/filter', [ReceptionistController::class, 'getFilteredEspecialistas'])->name('especialistas.filter');

    // Notificaciones
    Route::patch('/notificaciones/leer', [ReceptionistController::class, 'marcarNotificacionesLeidas'])->name('notificaciones.leer');

    // Reprogramación de citas (a solicitud del paciente por teléfono)
    Route::get('/booking/reschedule/{cita}', [ReceptionistController::class, 'reschedule'])->name('booking.reschedule');
});

// Admin Routes
Route::group(['middleware' => ['auth', 'role:admin'], 'prefix' => 'admin', 'as' => 'admin.'], function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('dashboard');
});

// Google Meet Integration
Route::middleware('auth')->group(function () {
    // Google OAuth
    Route::get('/google/auth', [GoogleAuthController::class, 'redirectToGoogle'])->name('google.auth');
    Route::get('/google/auth/callback', [GoogleAuthController::class, 'handleGoogleCallback'])->name('google.callback');

    // Meetings
    Route::get('/meetings/create', [MeetingController::class, 'create'])->name('meetings.create');
    Route::post('/meetings', [MeetingController::class, 'store'])->name('meetings.store');
    Route::post('/meetings/generate/{citaId}', [MeetingController::class, 'generateForAppointment'])->name('meetings.generate');
});
