<?php
session_start();
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}
require_once "config.php";

$id_usuario_actual = $_SESSION['id_usuario'];
$conversacion_id = isset($_GET['conversacion']) ? intval($_GET['conversacion']) : null;

// Marcar mensajes como leídos cuando se abre una conversación
if ($conversacion_id) {
    $sql_leido = "UPDATE mensajes SET leido = 1 WHERE id_destinatario = ? AND id_remitente = ? AND leido = 0";
    if ($stmt = $conexion->prepare($sql_leido)) {
        $stmt->bind_param("ii", $id_usuario_actual, $conversacion_id);
        $stmt->execute();
        $stmt->close();
    }
}

// Obtener todas las conversaciones
$sql = "SELECT 
    m.id_remitente, 
    m.id_destinatario,
    m.asunto, 
    m.contenido, 
    m.fecha_envio, 
    m.leido,
    remitente.nombre AS nombre_remitente,
    destinatario.nombre AS nombre_destinatario
FROM mensajes m
JOIN usuarios remitente ON m.id_remitente = remitente.id_usuario
JOIN usuarios destinatario ON m.id_destinatario = destinatario.id_usuario
WHERE m.id_remitente = ? OR m.id_destinatario = ?
ORDER BY m.fecha_envio DESC";

$todos_mensajes = [];
if ($stmt = $conexion->prepare($sql)) {
    $stmt->bind_param("ii", $id_usuario_actual, $id_usuario_actual);
    $stmt->execute();
    $result = $stmt->get_result();
    while ($row = $result->fetch_assoc()) {
        $todos_mensajes[] = $row;
    }
    $stmt->close();
}

// Agrupar mensajes por conversación
$conversaciones_map = [];
foreach ($todos_mensajes as $msg) {
    if ($msg['id_remitente'] == $id_usuario_actual) {
        $otro_usuario_id = $msg['id_destinatario'];
        $otro_usuario_nombre = $msg['nombre_destinatario'];
    } else {
        $otro_usuario_id = $msg['id_remitente'];
        $otro_usuario_nombre = $msg['nombre_remitente'];
    }
    
    if (!isset($conversaciones_map[$otro_usuario_id])) {
        $conversaciones_map[$otro_usuario_id] = [
            'otro_usuario_id' => $otro_usuario_id,
            'otro_usuario_nombre' => $otro_usuario_nombre,
            'ultimo_mensaje' => $msg['contenido'],
            'ultima_fecha' => $msg['fecha_envio'],
            'asunto' => $msg['asunto'],
            'no_leidos' => 0
        ];
    }
    
    if (strtotime($msg['fecha_envio']) > strtotime($conversaciones_map[$otro_usuario_id]['ultima_fecha'])) {
        $conversaciones_map[$otro_usuario_id]['ultimo_mensaje'] = $msg['contenido'];
        $conversaciones_map[$otro_usuario_id]['ultima_fecha'] = $msg['fecha_envio'];
        $conversaciones_map[$otro_usuario_id]['asunto'] = $msg['asunto'];
    }
    
    if ($msg['id_destinatario'] == $id_usuario_actual && $msg['leido'] == 0) {
        $conversaciones_map[$otro_usuario_id]['no_leidos']++;
    }
}

// ordenar por fecha más reciente
$conversaciones = array_values($conversaciones_map);
usort($conversaciones, function($a, $b) {
    return strtotime($b['ultima_fecha']) - strtotime($a['ultima_fecha']);
});

// Obtener mensajes de una conversación
$mensajes_conversacion = [];
if ($conversacion_id) {
    $sql_mensajes = "SELECT 
        m.id_mensaje,
        m.id_remitente,
        m.id_destinatario,
        m.asunto,
        m.contenido,
        m.fecha_envio,
        m.leido,
        u_rem.nombre AS nombre_remitente,
        u_dest.nombre AS nombre_destinatario
    FROM mensajes m
    JOIN usuarios u_rem ON m.id_remitente = u_rem.id_usuario
    JOIN usuarios u_dest ON m.id_destinatario = u_dest.id_usuario
    WHERE (m.id_remitente = ? AND m.id_destinatario = ?) 
       OR (m.id_remitente = ? AND m.id_destinatario = ?)
    ORDER BY m.fecha_envio ASC";
    
    if ($stmt = $conexion->prepare($sql_mensajes)) {
        $stmt->bind_param("iiii", $id_usuario_actual, $conversacion_id, $conversacion_id, $id_usuario_actual);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $mensajes_conversacion[] = $row;
        }
        $stmt->close();
    }
}

// Obtener nombre del otro usuario
$otro_usuario_nombre = '';
if ($conversacion_id && !empty($mensajes_conversacion)) {
    $primer_mensaje = $mensajes_conversacion[0];
    $otro_usuario_nombre = ($primer_mensaje['id_remitente'] == $id_usuario_actual) 
        ? $primer_mensaje['nombre_destinatario'] 
        : $primer_mensaje['nombre_remitente'];
}

$conexion->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mensajes - PetSociety</title>
    <link rel="stylesheet" href="estilos.css">
    <?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1): ?>
        <link rel="stylesheet" href="admin/admin.css">
    <?php endif; ?>
    <style>
        .messaging-container {
            display: flex;
            gap: 20px;
            margin-top: 20px;
            height: calc(100vh - 200px);
            min-height: 600px;
        }
        
        .conversations-list {
            width: 300px;
            background: #fff;
            border-radius: 8px;
            border: 1px solid #D9D9D9;
            overflow-y: auto;
            max-height: 100%;
        }
        
        .conversations-list h3 {
            padding: 15px;
            margin: 0;
            border-bottom: 2px solid #D9D9D9;
            color: #404040;
            font-size: 18px;
        }
        
        .conversation-item {
            padding: 15px;
            border-bottom: 1px solid #D9D9D9;
            cursor: pointer;
            transition: background-color 0.2s;
        }
        
        .conversation-item:hover {
            background-color: #F2F2F2;
        }
        
        .conversation-item.active {
            background-color: #E8E8E8;
            border-left: 4px solid #6B8E9F;
        }
        
        .conversation-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 8px;
        }
        
        .conversation-name {
            font-weight: 600;
            color: #404040;
            font-size: 14px;
        }
        
        .conversation-time {
            font-size: 12px;
            color: #8C8C8C;
        }
        
        .conversation-subject {
            font-size: 13px;
            color: #595959;
            margin-bottom: 5px;
            font-weight: 500;
        }
        
        .conversation-preview {
            font-size: 12px;
            color: #8C8C8C;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .unread-badge {
            background-color: #6B8E9F;
            color: white;
            border-radius: 12px;
            padding: 2px 8px;
            font-size: 11px;
            font-weight: 600;
        }
        
        .messages-view {
            flex: 1;
            background: #fff;
            border-radius: 8px;
            border: 1px solid #D9D9D9;
            display: flex;
            flex-direction: column;
        }
        
        .messages-view.empty {
            align-items: center;
            justify-content: center;
            color: #8C8C8C;
        }
        
        .messages-header {
            padding: 15px;
            border-bottom: 2px solid #D9D9D9;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .messages-header h3 {
            margin: 0;
            color: #404040;
            font-size: 18px;
        }
        
        .new-message-btn {
            background-color: #6B8E9F;
            color: white;
            padding: 8px 16px;
            border-radius: 5px;
            text-decoration: none;
            font-size: 14px;
            transition: background-color 0.3s;
        }
        
        .new-message-btn:hover {
            background-color: #5A7A8A;
        }
        
        .messages-area {
            flex: 1;
            overflow-y: auto;
            padding: 20px;
            display: flex;
            flex-direction: column;
            gap: 15px;
        }
        
        .message {
            max-width: 70%;
            padding: 12px 16px;
            border-radius: 12px;
            word-wrap: break-word;
        }
        
        .message.sent {
            align-self: flex-end;
            background-color: #6B8E9F;
            color: white;
            border-bottom-right-radius: 4px;
        }
        
        .message.received {
            align-self: flex-start;
            background-color: #F2F2F2;
            color: #404040;
            border-bottom-left-radius: 4px;
        }
        
        .message-meta {
            font-size: 11px;
            opacity: 0.8;
            margin-top: 5px;
        }
        
        .message-content {
            font-size: 14px;
            line-height: 1.5;
        }
        
        .message-form {
            padding: 15px;
            border-top: 2px solid #D9D9D9;
        }
        
        .message-form form {
            display: flex;
            gap: 10px;
        }
        
        .message-form textarea {
            flex: 1;
            padding: 10px;
            border: 1px solid #D9D9D9;
            border-radius: 5px;
            font-family: inherit;
            font-size: 14px;
            resize: none;
            min-height: 60px;
        }
        
        .message-form textarea:focus {
            outline: none;
            border-color: #6B8E9F;
        }
        
        .send-btn {
            background-color: #6B8E9F;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            transition: background-color 0.3s;
        }
        
        .send-btn:hover {
            background-color: #5A7A8A;
        }
        
        .empty-state {
            text-align: center;
            color: #8C8C8C;
        }
        
        .empty-state h3 {
            color: #404040;
            margin-bottom: 10px;
        }
        
        @media (max-width: 768px) {
            .messaging-container {
                flex-direction: column;
                height: auto;
            }
            
            .conversations-list {
                width: 100%;
                max-height: 300px;
            }
            
            .messages-view {
                min-height: 400px;
            }
            
            .message {
                max-width: 85%;
            }
        }
    </style>
</head>
<body>
    <header>
        <div class="container">
            <div id="branding">
                <h1><a href="index.php"><img src="img/logo1.png" alt="PetSociety Logo" class="header-logo"></a><a href="index.php">PetSociety</a></h1>
            </div>
            <nav>
                <button class="mobile-menu-toggle" aria-label="Toggle menu">
                    <span></span>
                    <span></span>
                    <span></span>
                </button>
                <ul class="nav-menu">
                    <?php if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true): ?>
                        <li><a href="index.php">Inicio</a></li>
                        <li><a href="refugios.php">Refugios</a></li>
                        <li><a href="buzon.php">Mensajes</a></li>
                        <?php if (isset($_SESSION['is_admin']) && $_SESSION['is_admin'] == 1): ?>
                            <li class="admin-panel-dropdown">
                                <span class="admin-panel-trigger">Panel de Administrador</span>
                                <div class="admin-submenu">
                                    <ul>
                                        <li><a href="admin/statistics.php">Estadísticas</a></li>
                                        <li><a href="admin/manage_publications.php">Administrar Publicaciones</a></li>
                                    </ul>
                                </div>
                            </li>
                        <?php endif; ?>
                    <?php else: ?>
                        <li><a href="index.php">Inicio</a></li>
                        <li><a href="refugios.php">Refugios</a></li>
                        <li><a href="login.php">Iniciar Sesión</a></li>
                        <li><a href="registro.php">Registrarse</a></li>
                    <?php endif; ?>
                    <?php if (isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true): ?>
                        <li class="user-menu mobile-user-menu">
                            <span class="user-menu-trigger">
                                <span class="user-icon"></span>
                                <span class="user-name"><?php echo htmlspecialchars($_SESSION["nombre"]); ?></span>
                            </span>
                            <div class="dropdown-menu">
                                <ul>
                                    <li><a href="mi_perfil.php">Mi Perfil</a></li>
                                    <li><a href="logout.php">Cerrar Sesión</a></li>
                                </ul>
                            </div>
                        </li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </header>

    <div class="container">
        <h2>Buzón de Entrada</h2>
        
        <div class="messaging-container">
            <!-- Lista de conversaciones -->
            <div class="conversations-list">
                <h3>Conversaciones</h3>
                <div style="padding: 15px; border-bottom: 2px solid #D9D9D9;">
                    <a href="index.php" class="new-message-btn" style="display: block; text-align: center;">Nuevo Mensaje</a>
                </div>
                <?php if (!empty($conversaciones)): ?>
                    <?php foreach ($conversaciones as $conv): ?>
                        <div class="conversation-item <?php echo ($conversacion_id == $conv['otro_usuario_id']) ? 'active' : ''; ?>"
                             onclick="window.location.href='buzon.php?conversacion=<?php echo $conv['otro_usuario_id']; ?>'">
                            <div class="conversation-header">
                                <span class="conversation-name"><?php echo htmlspecialchars($conv['otro_usuario_nombre']); ?></span>
                                <span class="conversation-time"><?php echo date("d/m H:i", strtotime($conv['ultima_fecha'])); ?></span>
                            </div>
                            <div class="conversation-subject"><?php echo htmlspecialchars($conv['asunto']); ?></div>
                            <div class="conversation-preview">
                                <?php if ($conv['no_leidos'] > 0): ?>
                                    <span class="unread-badge"><?php echo $conv['no_leidos']; ?></span>
                                <?php endif; ?>
                                <span><?php echo htmlspecialchars(mb_substr($conv['ultimo_mensaje'], 0, 50)); ?>...</span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="empty-state" style="padding: 20px; text-align: center; color: #8C8C8C;">
                        <p>No tienes conversaciones aún</p>
                    </div>
                <?php endif; ?>
            </div>
            
            <!-- Área de mensajes -->
            <div class="messages-view <?php echo empty($mensajes_conversacion) ? 'empty' : ''; ?>">
                <?php if ($conversacion_id && !empty($mensajes_conversacion)): ?>
                    <div class="messages-header">
                        <h3><?php echo htmlspecialchars($otro_usuario_nombre); ?></h3>
                    </div>
                    
                    <div class="messages-area">
                        <?php foreach ($mensajes_conversacion as $msg): ?>
                            <div class="message <?php echo ($msg['id_remitente'] == $id_usuario_actual) ? 'sent' : 'received'; ?>">
                                <div class="message-content"><?php echo nl2br(htmlspecialchars($msg['contenido'])); ?></div>
                                <div class="message-meta">
                                    <?php echo date("d/m/Y H:i", strtotime($msg['fecha_envio'])); ?>
                                    <?php if ($msg['id_remitente'] == $id_usuario_actual && $msg['leido']): ?>
                                        <span>✓ Leído</span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    
                    <div class="message-form">
                        <form action="procesar_mensaje.php" method="post">
                            <input type="hidden" name="id_destinatario" value="<?php echo $conversacion_id; ?>">
                            <input type="hidden" name="asunto" value="<?php echo htmlspecialchars($mensajes_conversacion[0]['asunto']); ?>">
                            <textarea name="contenido" placeholder="Escribe tu mensaje..." required></textarea>
                            <button type="submit" class="send-btn">Enviar</button>
                        </form>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <h3>Selecciona una conversación</h3>
                        <p>Elige una conversación de la lista para ver los mensajes</p>
                        <?php if (empty($conversaciones)): ?>
                            <a href="index.php" class="btn" style="margin-top: 15px; display: inline-block;">Explorar Publicaciones</a>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const mobileMenuToggle = document.querySelector('.mobile-menu-toggle');
            const navMenu = document.querySelector('.nav-menu');
            const mobileUserMenu = document.querySelector('.mobile-user-menu');
            
            if (mobileMenuToggle && navMenu) {
                mobileMenuToggle.addEventListener('click', function(e) {
                    e.stopPropagation();
                    navMenu.classList.toggle('active');
                    mobileMenuToggle.classList.toggle('active');
                });

                document.addEventListener('click', function(event) {
                    if (!navMenu.contains(event.target) && !mobileMenuToggle.contains(event.target)) {
                        navMenu.classList.remove('active');
                        mobileMenuToggle.classList.remove('active');
                    }
                });

                const navLinks = navMenu.querySelectorAll('a');
                navLinks.forEach(link => {
                    link.addEventListener('click', function() {
                        navMenu.classList.remove('active');
                        mobileMenuToggle.classList.remove('active');
                    });
                });
            }

            if (mobileUserMenu) {
                const userMenuTrigger = mobileUserMenu.querySelector('.user-menu-trigger');
                if (userMenuTrigger) {
                    userMenuTrigger.addEventListener('click', function(e) {
                        e.preventDefault();
                        e.stopPropagation();
                        mobileUserMenu.classList.toggle('active');
                    });
                }
            }
            
            const messagesArea = document.querySelector('.messages-area');
            if (messagesArea) {
                messagesArea.scrollTop = messagesArea.scrollHeight;
            }
        });
    </script>

    <?php include 'footer.php'; ?>
</body>
</html>
