<?php
include("conexion.php");

// --- AÑADIR CITA ---
if (isset($_POST['accion']) && $_POST['accion'] == 'agregar') {
    $idPropiedad = $_POST['idPropiedad'];
    $fechaVisita = $_POST['fechaVisita'];
    $horaInicio = $_POST['horaInicio'];
    $horaFin = $_POST['horaFin'];
    $nombreCliente = $_POST['nombreCliente'];
    $telefonoCliente = $_POST['telefonoCliente'];
    $correoCliente = $_POST['correoCliente'];
    $notas = $_POST['notas'];
    $idUsuario = $_POST['idUsuario'];

    // Validaciones de disponibilidad
    $verificarAgente = $conn->prepare("
        SELECT COUNT(*) as total
        FROM citas
        WHERE idUsuario = ? 
        AND fechaVisita = ?
        AND (
            (horaInicio <= ? AND horaFin >= ?) OR
            (horaInicio <= ? AND horaFin >= ?)
        )
    ");
    $verificarAgente->bind_param("isssss", $idUsuario, $fechaVisita, $horaInicio, $horaInicio, $horaFin, $horaFin);
    $verificarAgente->execute();
    $resA = $verificarAgente->get_result()->fetch_assoc();

    $verificarPropiedad = $conn->prepare("
        SELECT COUNT(*) as total
        FROM citas
        WHERE idPropiedad = ? 
        AND fechaVisita = ?
        AND (
            (horaInicio <= ? AND horaFin >= ?) OR
            (horaInicio <= ? AND horaFin >= ?)
        )
    ");
    $verificarPropiedad->bind_param("isssss", $idPropiedad, $fechaVisita, $horaInicio, $horaInicio, $horaFin, $horaFin);
    $verificarPropiedad->execute();
    $resP = $verificarPropiedad->get_result()->fetch_assoc();

    if ($resA['total'] >= 3) {
        echo "<script>alert('⚠️ Este agente ya tiene el máximo de 3 citas este día o está en conflicto de horario.'); window.location='citas.php';</script>";
        exit;
    }

    if ($resP['total'] >= 2) {
        echo "<script>alert('⚠️ Esta propiedad ya tiene 2 visitas en ese horario.'); window.location='citas.php';</script>";
        exit;
    }

    $sql = "INSERT INTO citas (idPropiedad, fechaVisita, horaInicio, horaFin, nombreCliente, telefonoCliente, correoCliente, notas, idUsuario)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isssssssi", $idPropiedad, $fechaVisita, $horaInicio, $horaFin, $nombreCliente, $telefonoCliente, $correoCliente, $notas, $idUsuario);

    if ($stmt->execute()) {
        echo "<script>alert('✅ Cita registrada correctamente'); window.location='citas.php';</script>";
    } else {
        echo "<script>alert('❌ Error al registrar cita: " . $stmt->error . "');</script>";
    }

    $stmt->close();
}

// --- CANCELAR CITA ---
if (isset($_POST['accion']) && $_POST['accion'] == 'cancelar') {
    $idCita = $_POST['idCita'];
    $motivo = $_POST['motivo'];
    $idUsuario = $_POST['idUsuario'];

    $sql = "UPDATE citas SET idEstado = 3, notas = CONCAT(notas, '\n[CANCELADA: ', ?, ']') WHERE idCita = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $motivo, $idCita);

    if ($stmt->execute()) {
        echo "<script>alert('🟠 Cita cancelada correctamente'); window.location='citas.php';</script>";
    } else {
        echo "<script>alert('❌ Error al cancelar cita: " . $stmt->error . "');</script>";
    }

    $stmt->close();
}

// --- DATOS PARA SELECTS ---
$propiedades = $conn->query("SELECT idPropiedad, titulo FROM propiedades ORDER BY titulo ASC");
$agentes = $conn->query("SELECT idUsuario, nombre FROM usuarios ORDER BY nombre ASC");

// --- CONSULTAR CITAS ---
$resultado = $conn->query("SELECT * FROM vista_citas_completas ORDER BY fechaVisita DESC");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Citas | Inmobiliaria</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #0C2340;
            --accent: #f5b341;
            --white: #FFFFFF;
            --light: #03035c;
            --dark: #1C1C1C;
            --secondary: #03035c;
            --highlight: #FFD166;
            --cta: #f7c923;
            --gradient: linear-gradient(135deg, #0C2340 0%, #1C3A5F 100%);
            --gradient-accent: linear-gradient(135deg, #f2d29a 0%, #f6b430 100%);
            --transition: all 0.5s cubic-bezier(0.68, -0.55, 0.265, 1.55);
        }

        body {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            color: var(--dark);
            font-family: "Poppins", sans-serif;
            min-height: 100vh;
        }

        .navbar-custom {
            background: var(--gradient);
            box-shadow: 0 4px 12px rgba(12, 35, 64, 0.15);
        }

        .navbar-brand {
            font-weight: 700;
            letter-spacing: 0.5px;
            color: var(--white) !important;
        }

        .card {
            border: none;
            border-radius: 16px;
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.08);
            transition: var(--transition);
            overflow: hidden;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 30px rgba(0, 0, 0, 0.12);
        }

        .card-header {
            background: var(--gradient);
            color: var(--white);
            font-weight: 600;
            letter-spacing: 0.5px;
            padding: 1.25rem 1.5rem;
            border-bottom: none;
        }

        .card-header i {
            color: var(--accent);
        }

        .btn-primary {
            background: var(--gradient);
            border: none;
            font-weight: 500;
            letter-spacing: 0.5px;
            transition: var(--transition);
            padding: 0.75rem 1.5rem;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(12, 35, 64, 0.3);
        }

        .btn-success {
            background: var(--gradient-accent);
            border: none;
            color: var(--primary);
            font-weight: 600;
            transition: var(--transition);
            padding: 0.75rem 1.5rem;
        }

        .btn-success:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 15px rgba(245, 179, 65, 0.4);
            color: var(--primary);
        }

        .btn-danger {
            background: #e74c3c;
            border: none;
            transition: var(--transition);
        }

        .btn-danger:hover {
            background: #c0392b;
            transform: translateY(-2px);
        }

        .form-control, .form-select {
            border-radius: 10px;
            padding: 0.75rem 1rem;
            border: 1px solid #e1e5eb;
            transition: var(--transition);
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--accent);
            box-shadow: 0 0 0 0.25rem rgba(245, 179, 65, 0.25);
        }

        .form-label {
            font-weight: 500;
            margin-bottom: 0.5rem;
            color: var(--primary);
        }

        table {
            border-collapse: separate;
            border-spacing: 0;
        }

        table thead {
            background: var(--gradient);
            color: var(--white);
        }

        table thead th {
            border: none;
            padding: 1rem 0.75rem;
            font-weight: 600;
            letter-spacing: 0.5px;
        }

        table tbody tr {
            transition: var(--transition);
        }

        table tbody tr:hover {
            background-color: rgba(245, 179, 65, 0.05);
        }

        table tbody td {
            padding: 1rem 0.75rem;
            border-bottom: 1px solid #e9ecef;
            vertical-align: middle;
        }

        .badge {
            font-weight: 500;
            padding: 0.5rem 0.75rem;
            border-radius: 8px;
        }

        .section-title {
            color: var(--primary);
            font-weight: 700;
            position: relative;
            padding-bottom: 0.75rem;
            margin-bottom: 1.5rem;
        }

        .section-title:after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 60px;
            height: 4px;
            background: var(--gradient-accent);
            border-radius: 2px;
        }

        .hero-section {
            background: var(--gradient);
            color: var(--white);
            padding: 3rem 0;
            border-radius: 0 0 20px 20px;
            margin-bottom: 2rem;
            text-align: center;
        }

        .hero-section h1 {
            font-weight: 700;
            margin-bottom: 0.5rem;
        }

        .hero-section p {
            opacity: 0.9;
            font-weight: 300;
        }

        .floating-action {
            position: fixed;
            bottom: 2rem;
            right: 2rem;
            z-index: 100;
            background: var(--gradient-accent);
            color: var(--primary);
            width: 60px;
            height: 60px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 6px 20px rgba(245, 179, 65, 0.4);
            transition: var(--transition);
            font-size: 1.5rem;
            text-decoration: none;
        }

        .floating-action:hover {
            transform: translateY(-5px) scale(1.1);
            color: var(--primary);
            box-shadow: 0 10px 25px rgba(245, 179, 65, 0.5);
        }

        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }

        .status-indicator {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            display: inline-block;
        }

        .status-pending .status-indicator {
            background-color: #3498db;
        }

        .status-completed .status-indicator {
            background-color: #2ecc71;
        }

        .status-cancelled .status-indicator {
            background-color: #e74c3c;
        }

        .empty-state {
            text-align: center;
            padding: 3rem 1rem;
            color: #6c757d;
        }

        .empty-state i {
            font-size: 4rem;
            margin-bottom: 1rem;
            color: #dee2e6;
        }

        /* Notificaciones personalizadas */
        .notification-container {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
            max-width: 350px;
        }

        .notification {
            background: white;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
            padding: 1.25rem;
            margin-bottom: 1rem;
            display: flex;
            align-items: flex-start;
            gap: 1rem;
            transform: translateX(400px);
            opacity: 0;
            transition: all 0.5s cubic-bezier(0.68, -0.55, 0.265, 1.55);
            border-left: 4px solid;
        }

        .notification.show {
            transform: translateX(0);
            opacity: 1;
        }

        .notification.success {
            border-left-color: #2ecc71;
        }

        .notification.error {
            border-left-color: #e74c3c;
        }

        .notification.warning {
            border-left-color: #f39c12;
        }

        .notification.info {
            border-left-color: #3498db;
        }

        .notification-icon {
            font-size: 1.5rem;
            flex-shrink: 0;
        }

        .notification.success .notification-icon {
            color: #2ecc71;
        }

        .notification.error .notification-icon {
            color: #e74c3c;
        }

        .notification.warning .notification-icon {
            color: #f39c12;
        }

        .notification.info .notification-icon {
            color: #3498db;
        }

        .notification-content {
            flex-grow: 1;
        }

        .notification-title {
            font-weight: 600;
            margin-bottom: 0.25rem;
            color: var(--primary);
        }

        .notification-message {
            color: #6c757d;
            font-size: 0.9rem;
            margin-bottom: 0;
        }

        .notification-close {
            background: none;
            border: none;
            color: #adb5bd;
            cursor: pointer;
            font-size: 1rem;
            transition: color 0.3s;
            flex-shrink: 0;
        }

        .notification-close:hover {
            color: #6c757d;
        }

        .loading-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.8);
            display: flex;
            justify-content: center;
            align-items: center;
            z-index: 9999;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s;
        }

        .loading-overlay.show {
            opacity: 1;
            visibility: visible;
        }

        .spinner {
            width: 50px;
            height: 50px;
            border: 5px solid rgba(12, 35, 64, 0.2);
            border-radius: 50%;
            border-top-color: var(--primary);
            animation: spin 1s ease-in-out infinite;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        @media (max-width: 768px) {
            .hero-section {
                padding: 2rem 0;
            }
            
            .card-body {
                padding: 1.25rem;
            }
            
            table {
                font-size: 0.875rem;
            }

            .notification-container {
                top: 10px;
                right: 10px;
                left: 10px;
                max-width: none;
            }
        }
    </style>
</head>
<body>
    <!-- Notificaciones -->
    <div class="notification-container" id="notificationContainer"></div>

    <!-- Loading Overlay -->
    <div class="loading-overlay" id="loadingOverlay">
        <div class="spinner"></div>
    </div>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark navbar-custom">
        <div class="container">
            <a class="navbar-brand" href="#">
                <i class="fa-solid fa-building me-2"></i>Inmobiliaria
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="#"><i class="fa-solid fa-calendar-days me-1"></i> Citas</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#"><i class="fa-solid fa-house me-1"></i> Propiedades</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#"><i class="fa-solid fa-users me-1"></i> Agentes</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#"><i class="fa-solid fa-chart-line me-1"></i> Reportes</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <div class="hero-section">
        <div class="container">
            <h1><i class="fa-solid fa-calendar-check me-2"></i>Gestión de Citas</h1>
            <p class="lead">Programa, gestiona y realiza seguimiento de todas las visitas a propiedades</p>
        </div>
    </div>

    <div class="container py-4">
        <!-- Formulario de registro -->
        <div class="card mb-5">
            <div class="card-header">
                <i class="fa-solid fa-plus me-2"></i>Registrar Nueva Cita
            </div>
            <div class="card-body">
                <form id="formAgregarCita" method="POST">
                    <input type="hidden" name="accion" value="agregar">

                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <label class="form-label">Propiedad</label>
                            <select name="idPropiedad" class="form-select" required>
                                <option value="">Seleccione una propiedad...</option>
                                <?php while ($p = $propiedades->fetch_assoc()) { ?>
                                    <option value="<?= $p['idPropiedad'] ?>"><?= htmlspecialchars($p['titulo']) ?></option>
                                <?php } ?>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Agente</label>
                            <select name="idUsuario" class="form-select" required>
                                <option value="">Seleccione un agente...</option>
                                <?php while ($a = $agentes->fetch_assoc()) { ?>
                                    <option value="<?= $a['idUsuario'] ?>"><?= htmlspecialchars($a['nombre']) ?></option>
                                <?php } ?>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label">Fecha</label>
                            <input type="date" name="fechaVisita" class="form-control" required>
                        </div>
                        <div class="col-md-1">
                            <label class="form-label">Inicio</label>
                            <input type="time" name="horaInicio" class="form-control" required>
                        </div>
                        <div class="col-md-1">
                            <label class="form-label">Fin</label>
                            <input type="time" name="horaFin" class="form-control" required>
                        </div>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-md-4">
                            <label class="form-label">Cliente</label>
                            <input type="text" name="nombreCliente" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Teléfono</label>
                            <input type="text" name="telefonoCliente" class="form-control" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Correo</label>
                            <input type="email" name="correoCliente" class="form-control" required>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Notas adicionales</label>
                        <textarea name="notas" class="form-control" rows="2" placeholder="Agregue cualquier información adicional relevante..."></textarea>
                    </div>

                    <button type="submit" class="btn btn-success w-100 py-3 fw-bold">
                        <i class="fa-solid fa-calendar-plus me-2"></i>Agendar Cita
                    </button>
                </form>
            </div>
        </div>

        <!-- Listado de citas -->
        <div class="card">
            <div class="card-header">
                <i class="fa-solid fa-list me-2"></i>Listado de Citas
            </div>
            <div class="card-body" id="citasContainer">
                <?php if ($resultado->num_rows > 0): ?>
                    <div class="table-responsive">
                        <table class="table align-middle table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Propiedad</th>
                                    <th>Cliente</th>
                                    <th>Fecha</th>
                                    <th>Hora</th>
                                    <th>Estado</th>
                                    <th>Agente</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($fila = $resultado->fetch_assoc()) { ?>
                                    <tr>
                                        <td class="fw-bold">#<?= $fila['idCita'] ?></td>
                                        <td><?= htmlspecialchars($fila['propiedad_titulo']) ?></td>
                                        <td>
                                            <div class="d-flex flex-column">
                                                <span class="fw-medium"><?= htmlspecialchars($fila['nombreCliente']) ?></span>
                                                <small class="text-muted"><?= htmlspecialchars($fila['correoCliente']) ?></small>
                                            </div>
                                        </td>
                                        <td><?= date('d/m/Y', strtotime($fila['fechaVisita'])) ?></td>
                                        <td><?= date('H:i', strtotime($fila['horaInicio'])) ?> - <?= date('H:i', strtotime($fila['horaFin'])) ?></td>
                                        <td>
                                            <?php
                                            $statusClass = '';
                                            if ($fila['estado'] === 'Cancelada') {
                                                $statusClass = 'status-cancelled';
                                                $badgeClass = 'bg-danger';
                                            } elseif ($fila['estado'] === 'Completada') {
                                                $statusClass = 'status-completed';
                                                $badgeClass = 'bg-success';
                                            } else {
                                                $statusClass = 'status-pending';
                                                $badgeClass = 'bg-info';
                                            }
                                            ?>
                                            <div class="status-badge <?= $statusClass ?>">
                                                <span class="status-indicator"></span>
                                                <span class="badge <?= $badgeClass ?>"><?= htmlspecialchars($fila['estado']) ?></span>
                                            </div>
                                        </td>
                                        <td><?= htmlspecialchars($fila['agente_asignado']) ?></td>
                                        <td>
                                            <?php if ($fila['estado'] != 'Cancelada' && $fila['estado'] != 'Completada') { ?>
                                                <form method="POST" class="formCancelarCita d-flex align-items-center">
                                                    <input type="hidden" name="accion" value="cancelar">
                                                    <input type="hidden" name="idCita" value="<?= $fila['idCita'] ?>">
                                                    <input type="hidden" name="idUsuario" value="<?= $fila['idUsuario'] ?>">
                                                    <input type="text" name="motivo" placeholder="Motivo de cancelación" class="form-control form-control-sm me-2" required>
                                                    <button type="submit" class="btn btn-danger btn-sm" title="Cancelar cita">
                                                        <i class="fa-solid fa-ban"></i>
                                                    </button>
                                                </form>
                                            <?php } else { ?>
                                                <span class="text-muted small">No disponible</span>
                                            <?php } ?>
                                        </td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fa-regular fa-calendar-xmark"></i>
                        <h4 class="text-muted">No hay citas programadas</h4>
                        <p class="text-muted">Comienza agregando una nueva cita usando el formulario superior.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Floating Action Button -->
    <a href="#" class="floating-action" data-bs-toggle="tooltip" data-bs-placement="left" title="Agendar nueva cita">
        <i class="fa-solid fa-plus"></i>
    </a>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Función para mostrar notificaciones
        function showNotification(type, title, message, duration = 5000) {
            const container = document.getElementById('notificationContainer');
            const notification = document.createElement('div');
            notification.className = `notification ${type}`;
            
            let iconClass;
            switch(type) {
                case 'success':
                    iconClass = 'fa-solid fa-circle-check';
                    break;
                case 'error':
                    iconClass = 'fa-solid fa-circle-exclamation';
                    break;
                case 'warning':
                    iconClass = 'fa-solid fa-triangle-exclamation';
                    break;
                case 'info':
                    iconClass = 'fa-solid fa-circle-info';
                    break;
            }
            
            notification.innerHTML = `
                <div class="notification-icon">
                    <i class="${iconClass}"></i>
                </div>
                <div class="notification-content">
                    <div class="notification-title">${title}</div>
                    <div class="notification-message">${message}</div>
                </div>
                <button class="notification-close">
                    <i class="fa-solid fa-times"></i>
                </button>
            `;
            
            container.appendChild(notification);
            
            // Mostrar notificación con animación
            setTimeout(() => {
                notification.classList.add('show');
            }, 10);
            
            // Configurar botón de cerrar
            const closeBtn = notification.querySelector('.notification-close');
            closeBtn.addEventListener('click', () => {
                hideNotification(notification);
            });
            
            // Auto-ocultar después del tiempo especificado
            if (duration > 0) {
                setTimeout(() => {
                    hideNotification(notification);
                }, duration);
            }
        }
        
        function hideNotification(notification) {
            notification.classList.remove('show');
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.parentNode.removeChild(notification);
                }
            }, 500);
        }
        
        // Función para mostrar/ocultar loading
        function toggleLoading(show) {
            const overlay = document.getElementById('loadingOverlay');
            if (show) {
                overlay.classList.add('show');
            } else {
                overlay.classList.remove('show');
            }
        }
        
        // Función para actualizar la tabla de citas
        function actualizarTablaCitas() {
            toggleLoading(true);
            
            fetch('citas.php?actualizar=1')
                .then(response => response.text())
                .then(html => {
                    // Extraer solo la parte de la tabla del HTML
                    const tempDiv = document.createElement('div');
                    tempDiv.innerHTML = html;
                    const nuevaTabla = tempDiv.querySelector('#citasContainer');
                    
                    if (nuevaTabla) {
                        document.getElementById('citasContainer').innerHTML = nuevaTabla.innerHTML;
                        // Reasignar eventos a los formularios de cancelación
                        asignarEventosCancelacion();
                    }
                    
                    toggleLoading(false);
                })
                .catch(error => {
                    console.error('Error al actualizar citas:', error);
                    toggleLoading(false);
                    showNotification('error', 'Error', 'No se pudo actualizar la lista de citas');
                });
        }
        
        // Asignar eventos a los formularios de cancelación
        function asignarEventosCancelacion() {
            document.querySelectorAll('.formCancelarCita').forEach(form => {
                form.addEventListener('submit', function(e) {
                    e.preventDefault();
                    
                    const formData = new FormData(this);
                    const idCita = formData.get('idCita');
                    const motivo = formData.get('motivo');
                    
                    if (!motivo.trim()) {
                        showNotification('warning', 'Advertencia', 'Por favor, ingresa un motivo para cancelar la cita');
                        return;
                    }
                    
                    toggleLoading(true);
                    
                    fetch('citas.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.text())
                    .then(data => {
                        toggleLoading(false);
                        
                        if (data.includes('Cita cancelada correctamente')) {
                            showNotification('success', 'Éxito', 'La cita ha sido cancelada correctamente');
                            // Limpiar campo de motivo
                            this.querySelector('input[name="motivo"]').value = '';
                            // Actualizar tabla
                            actualizarTablaCitas();
                        } else {
                            showNotification('error', 'Error', 'No se pudo cancelar la cita');
                        }
                    })
                    .catch(error => {
                        toggleLoading(false);
                        console.error('Error:', error);
                        showNotification('error', 'Error', 'Ocurrió un error al cancelar la cita');
                    });
                });
            });
        }
        
        // Asignar evento al formulario de agregar cita
        document.getElementById('formAgregarCita').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            
            toggleLoading(true);
            
            fetch('citas.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.text())
            .then(data => {
                toggleLoading(false);
                
                if (data.includes('Cita registrada correctamente')) {
                    showNotification('success', 'Éxito', 'La cita ha sido registrada correctamente');
                    // Limpiar formulario
                    this.reset();
                    // Actualizar tabla
                    actualizarTablaCitas();
                } else if (data.includes('máximo de 3 citas')) {
                    showNotification('warning', 'No disponible', 'Este agente ya tiene el máximo de 3 citas este día o está en conflicto de horario');
                } else if (data.includes('2 visitas en ese horario')) {
                    showNotification('warning', 'No disponible', 'Esta propiedad ya tiene 2 visitas en ese horario');
                } else {
                    showNotification('error', 'Error', 'No se pudo registrar la cita');
                }
            })
            .catch(error => {
                toggleLoading(false);
                console.error('Error:', error);
                showNotification('error', 'Error', 'Ocurrió un error al registrar la cita');
            });
        });
        
        // Inicializar eventos
        document.addEventListener('DOMContentLoaded', function() {
            // Inicializar tooltips de Bootstrap
            var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
            var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl)
            });
            
            // Scroll suave al formulario al hacer clic en el botón flotante
            document.querySelector('.floating-action').addEventListener('click', function(e) {
                e.preventDefault();
                document.querySelector('.card:first-child').scrollIntoView({ 
                    behavior: 'smooth' 
                });
            });
            
            // Asignar eventos a formularios de cancelación
            asignarEventosCancelacion();
        });
    </script>
</body>
</html>
