<?php

return [
    'available_actions' => [
        'register_patients' => [
            'label' => 'Registrar pacientes',
            'description' => 'Crear y gestionar pacientes desde recepción.',
        ],
        'book_appointments' => [
            'label' => 'Agendar citas',
            'description' => 'Reservar citas y administrar la agenda.',
        ],
        'manage_services' => [
            'label' => 'Gestionar servicios',
            'description' => 'Acceder a la gestión de servicios y mostrar el enlace de Servicios en la topbar.',
        ],
        'view_reports' => [
            'label' => 'Ver reportes',
            'description' => 'Acceder a informes financieros y métricas operativas.',
        ],
        // Granular report permissions
        'reportes_finanzas' => [
            'label' => 'Ver reportes financieros',
            'description' => 'Acceder al reporte de finanzas.',
        ],
        'reportes_desempeno' => [
            'label' => 'Ver reportes de desempeño',
            'description' => 'Acceder al reporte de desempeño.',
        ],
        'reportes_ocupacion' => [
            'label' => 'Ver reportes de ocupación',
            'description' => 'Acceder al reporte de ocupación.',
        ],
        'reportes_control' => [
            'label' => 'Ver reportes de control',
            'description' => 'Acceder al reporte de control operativo.',
        ],
        'reportes_auditoria' => [
            'label' => 'Ver reportes de auditoría',
            'description' => 'Acceder al reporte de auditoría.',
        ],
        'reportes_drill' => [
            'label' => 'Ver reportes drill',
            'description' => 'Acceder a vistas detalladas (drill) de métricas.',
        ],
        // Granular service permissions
        'servicios_index' => [ 'label' => 'Listar servicios', 'description' => 'Ver la lista de servicios.' ],
        'servicios_create' => [ 'label' => 'Crear servicio', 'description' => 'Acceder al formulario de creación de servicios.' ],
        'servicios_edit' => [ 'label' => 'Editar servicio', 'description' => 'Acceder al formulario de edición de servicios.' ],
        'servicios_toggle' => [ 'label' => 'Activar/desactivar servicio', 'description' => 'Cambiar estado activo de un servicio.' ],
        'servicios_destroy' => [ 'label' => 'Eliminar servicio', 'description' => 'Eliminar un servicio del sistema.' ],
        // Granular especialistas permissions
        'manage_specialists' => [ 'label' => 'Gestionar especialistas', 'description' => 'Acceder a la gestión de especialistas y mostrar el enlace de Especialistas en la topbar.' ],
        'especialistas_index' => [ 'label' => 'Listar especialistas', 'description' => 'Ver la lista de especialistas.' ],
        'especialistas_create' => [ 'label' => 'Crear especialista', 'description' => 'Acceder al formulario de creación de especialistas.' ],
        'especialistas_edit' => [ 'label' => 'Editar especialista', 'description' => 'Acceder al formulario de edición de especialistas.' ],
        // Granular usuarios permissions
        'manage_users' => [ 'label' => 'Gestionar usuarios', 'description' => 'Acceder a la gestión de usuarios y mostrar el enlace de Usuarios en la topbar.' ],
        'usuarios_index' => [ 'label' => 'Listar usuarios', 'description' => 'Ver la lista de usuarios del sistema.' ],
        'usuarios_toggle_estado' => [ 'label' => 'Cambiar estado de usuario', 'description' => 'Activar o desactivar cuentas de usuario.' ],
        // Categorías
        'categorias_index' => [ 'label' => 'Listar categorías', 'description' => 'Ver la lista de categorías de servicios.' ],
        'categorias_create' => [ 'label' => 'Crear categoría', 'description' => 'Acceder al formulario de creación de categorías.' ],
        'categorias_edit' => [ 'label' => 'Editar categoría', 'description' => 'Acceder al formulario de edición de categorías.' ],
        'categorias_toggle' => [ 'label' => 'Activar/desactivar categoría', 'description' => 'Cambiar estado de una categoría.' ],
        'categorias_destroy' => [ 'label' => 'Eliminar categoría', 'description' => 'Eliminar una categoría del sistema.' ],
        // Lista de espera
        'lista_espera_index' => [ 'label' => 'Ver lista de espera', 'description' => 'Acceder a la lista de espera.' ],
        'lista_espera_store' => [ 'label' => 'Agregar lista de espera', 'description' => 'Agregar un registro a la lista de espera.' ],
        'lista_espera_update_estado' => [ 'label' => 'Actualizar estado lista de espera', 'description' => 'Actualizar estado de un registro en lista de espera.' ],
        'lista_espera_destroy' => [ 'label' => 'Eliminar de lista de espera', 'description' => 'Eliminar un registro de la lista de espera.' ],
        // Soporte
        'soporte_index' => [ 'label' => 'Ver bandeja de soporte', 'description' => 'Acceder a la bandeja de soporte.' ],
        'soporte_respond' => [ 'label' => 'Responder soporte', 'description' => 'Responder una incidencia de soporte.' ],
        'soporte_update_estado' => [ 'label' => 'Actualizar estado soporte', 'description' => 'Cambiar estado de un ticket de soporte.' ],
        // Roles
        'roles_index' => [ 'label' => 'Ver roles', 'description' => 'Acceder al listado de roles y permisos.' ],
        'roles_update' => [ 'label' => 'Actualizar roles', 'description' => 'Modificar acciones asignadas a un rol.' ],
        // Pacientes y citas
        'pacientes_index' => [ 'label' => 'Ver pacientes', 'description' => 'Acceder al listado de pacientes.' ],
        'citas_index' => [ 'label' => 'Ver citas', 'description' => 'Acceder al listado de citas.' ],
    ],

    // Agrupación de acciones para UI: etiquetas y lista de claves por grupo.
    // Esto no cambia la estructura de `available_actions` (seguimos manteniendo
    // el listado plano para compatibilidad), pero facilita renderizar la UI
    // de permisos por secciones.
    'groups' => [
        'pacientes' => [
            'label' => 'Pacientes',
            'actions' => [
                'pacientes_index',
                'register_patients',
                // ficha / notas / crisis se exponen como reportes o acciones específicas
            ],
        ],
        'citas' => [
            'label' => 'Citas',
            'actions' => [
                'book_appointments',
                'citas_index',
            ],
        ],
        'servicios' => [
            'label' => 'Servicios',
            'actions' => [
                'manage_services',
                'servicios_index',
                'servicios_create',
                'servicios_edit',
                'servicios_toggle',
                'servicios_destroy',
            ],
        ],
        'especialistas' => [
            'label' => 'Especialistas',
            'actions' => [
                'manage_specialists',
                'especialistas_index',
                'especialistas_create',
                'especialistas_edit',
            ],
        ],
        'usuarios' => [
            'label' => 'Usuarios',
            'actions' => [
                'manage_users',
                'usuarios_index',
                'usuarios_toggle_estado',
            ],
        ],
        'reportes' => [
            'label' => 'Reportes',
            'actions' => [
                'view_reports',
                'reportes_finanzas',
                'reportes_desempeno',
                'reportes_ocupacion',
                'reportes_control',
                'reportes_auditoria',
                'reportes_drill',
            ],
        ],
        'categorias' => [
            'label' => 'Categorías',
            'actions' => [
                'categorias_index',
                'categorias_create',
                'categorias_edit',
                'categorias_toggle',
                'categorias_destroy',
            ],
        ],
        'lista_espera' => [
            'label' => 'Lista de espera',
            'actions' => [
                'lista_espera_index',
                'lista_espera_store',
                'lista_espera_update_estado',
                'lista_espera_destroy',
            ],
        ],
        'soporte' => [
            'label' => 'Soporte',
            'actions' => [
                'soporte_index',
                'soporte_respond',
                'soporte_update_estado',
            ],
        ],
        'roles' => [
            'label' => 'Roles y permisos',
            'actions' => [
                'roles_index',
                'roles_update',
            ],
        ],
    ],

    'defaults' => [
        'admin' => [
            // base/general actions
            'register_patients',
            'book_appointments',
            'view_reports',

            // reportes (granulares)
            'reportes_finanzas',
            'reportes_desempeno',
            'reportes_ocupacion',
            'reportes_control',
            'reportes_auditoria',
            'reportes_drill',

            // portal de pacientes

            // servicios
            'manage_services',
            'servicios_index',
            'servicios_create',
            'servicios_edit',
            'servicios_toggle',
            'servicios_destroy',

            // especialistas
            'manage_specialists',
            'especialistas_index',
            'especialistas_create',
            'especialistas_edit',

            // categorias
            'categorias_index',
            'categorias_create',
            'categorias_edit',
            'categorias_toggle',
            'categorias_destroy',

            // lista de espera
            'lista_espera_index',
            'lista_espera_store',
            'lista_espera_update_estado',
            'lista_espera_destroy',

            // soporte
            'soporte_index',
            'soporte_respond',
            'soporte_update_estado',

            // usuarios y roles
            'manage_users',
            'usuarios_index',
            'usuarios_toggle_estado',
            'roles_index',
            'roles_update',

            // pacientes y citas
            'pacientes_index',
            'citas_index',
        ],
        'doctor' => [
            'book_appointments',
        ],
        'receptionist' => [
            'register_patients',
            'book_appointments',
        ],
        'patient' => [
        ],
    ],
];
