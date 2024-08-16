<?php
include('includes/header.php');
include('db.php');
session_start();

// Verificar si el usuario está logueado como administrador
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Manejo de eliminación de usuarios
if (isset($_GET['eliminar'])) {
    $id = $_GET['eliminar'];
    $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
    $stmt->execute([$id]);
    header("Location: administrador.php");
    exit();
}

// Filtrado por curso o saldo
$filtro_curso = isset($_GET['curso']) ? $_GET['curso'] : '';
$filtro_saldo = isset($_GET['orden']) ? $_GET['orden'] : '';

$query = "SELECT * FROM users WHERE role = 'student'";
$params = [];

if ($filtro_curso) {
    $query .= " AND course = ?";
    $params[] = $filtro_curso;
}

if ($filtro_saldo == 'alto') {
    $query .= " ORDER BY balance DESC";
} elseif ($filtro_saldo == 'bajo') {
    $query .= " ORDER BY balance ASC";
}

$stmt = $pdo->prepare($query);
$stmt->execute($params);
$usuarios = $stmt->fetchAll();
?>

<div class="container mt-5">
    <h2>Panel de Administración</h2>
    
    <form method="GET" action="administrador.php" class="mb-4">
        <div class="form-row">
            <div class="col">
                <input type="text" name="curso" class="form-control" placeholder="Buscar por curso" value="<?php echo htmlspecialchars($filtro_curso); ?>">
            </div>
            <div class="col">
                <select name="orden" class="form-control">
                    <option value="">Ordenar por saldo</option>
                    <option value="alto" <?php if ($filtro_saldo == 'alto') echo 'selected'; ?>>Mayor a menor</option>
                    <option value="bajo" <?php if ($filtro_saldo == 'bajo') echo 'selected'; ?>>Menor a mayor</option>
                </select>
            </div>
            <div class="col">
                <button type="submit" class="btn btn-primary">Filtrar</button>
            </div>
        </div>
    </form>

    <table class="table table-striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>Nombre</th>
                <th>Apellido</th>
                <th>Curso</th>
                <th>Año</th>
                <th>Saldo</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($usuarios as $usuario): ?>
            <tr>
                <td><?php echo $usuario['id']; ?></td>
                <td><?php echo $usuario['name']; ?></td>
                <td><?php echo $usuario['lastname']; ?></td>
                <td><?php echo $usuario['course']; ?></td>
                <td><?php echo $usuario['year']; ?></td>
                <td><?php echo $usuario['balance']; ?></td>
                <td>
                    <a href="editar_usuario.php?id=<?php echo $usuario['id']; ?>" class="btn btn-warning btn-sm">Editar</a>
                    <a href="administrador.php?eliminar=<?php echo $usuario['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('¿Estás seguro de que deseas eliminar este usuario?');">Eliminar</a>
                    <a href="historial.php?id=<?php echo $usuario['id']; ?>" class="btn btn-info btn-sm">Historial</a>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <a href="registro_admin.php" class="btn btn-primary">Registrar Nuevo Usuario</a>
    <a href="logout.php" class="btn btn-danger">Cerrar Sesión</a>
    <button class="btn btn-secondary" onclick="history.back()">Volver Atrás</button>
    <a href="dashboard.php" class="btn btn-secondary">Estadisticas</a>
</div>

<?php include('includes/footer.php'); ?>
