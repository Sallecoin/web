<?php
include('includes/header.php');
include('db.php');
session_start();

// Verificar si el usuario está logueado como administrador
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Obtener ID del usuario a editar
if (!isset($_GET['id'])) {
    header("Location: administrador.php");
    exit();
}

$user_id = $_GET['id'];

// Obtener información del usuario
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if (!$user) {
    echo "<div class='alert alert-danger'>Usuario no encontrado.</div>";
    include('includes/footer.php');
    exit();
}

// Manejar la edición del usuario
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = $_POST['nombre'];
    $apellido = $_POST['apellido'];
    $rut = $_POST['rut'];
    $curso = $_POST['curso'];
    $anio = $_POST['anio'];
    $balance = $_POST['balance'];
    $rol = $_POST['rol'];
    $nueva_contrasena = $_POST['nueva_contrasena'];

    // Preparar la consulta SQL para actualizar los datos del usuario
    $sql = "UPDATE users SET name = ?, lastname = ?, rut = ?, course = ?, year = ?, balance = ?, role = ?";

    // Si se ha introducido una nueva contraseña, la actualizamos también
    $params = [$nombre, $apellido, $rut, $curso, $anio, $balance, $rol, $user_id];

    if (!empty($nueva_contrasena)) {
        $hashed_password = password_hash($nueva_contrasena, PASSWORD_BCRYPT);
        $sql .= ", password = ?";
        array_splice($params, 7, 0, $hashed_password); // Insertar la contraseña en los parámetros
    }

    $sql .= " WHERE id = ?";

    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);

    echo "<div class='alert alert-success'>Usuario actualizado con éxito.</div>";
    header("Location: administrador.php");
    exit();
}
?>

<div class="container mt-5">
    <h2>Editar Usuario</h2>
    <form action="editar_usuario.php?id=<?php echo $user_id; ?>" method="POST">
        <div class="form-group">
            <label for="nombre">Nombre</label>
            <input type="text" class="form-control" id="nombre" name="nombre" value="<?php echo htmlspecialchars($user['name']); ?>" required>
        </div>
        <div class="form-group">
            <label for="apellido">Apellido</label>
            <input type="text" class="form-control" id="apellido" name="apellido" value="<?php echo htmlspecialchars($user['lastname']); ?>" required>
        </div>
        <div class="form-group">
            <label for="rut">RUT</label>
            <input type="text" class="form-control" id="rut" name="rut" value="<?php echo htmlspecialchars($user['rut']); ?>" required>
        </div>
        <div class="form-group">
            <label for="curso">Curso</label>
            <select class="form-control" id="curso" name="curso" required>
                <option value="7°" <?php if ($user['course'] == "7°") echo 'selected'; ?>>7°</option>
                <option value="8°" <?php if ($user['course'] == "8°") echo 'selected'; ?>>8°</option>
                <option value="1°A" <?php if ($user['course'] == "1°A") echo 'selected'; ?>>1°A</option>
                <option value="1°B" <?php if ($user['course'] == "1°B") echo 'selected'; ?>>1°B</option>
                <option value="1°C" <?php if ($user['course'] == "1°C") echo 'selected'; ?>>1°C</option>
                <option value="2°A" <?php if ($user['course'] == "2°A") echo 'selected'; ?>>2°A</option>
                <option value="2°B" <?php if ($user['course'] == "2°B") echo 'selected'; ?>>2°B</option>
                <option value="2°C" <?php if ($user['course'] == "2°C") echo 'selected'; ?>>2°C</option>
            </select>
        </div>
        <div class="form-group">
            <label for="anio">Año</label>
            <input type="number" class="form-control" id="anio" name="anio" value="<?php echo $user['year']; ?>" required>
        </div>
        <div class="form-group">
            <label for="balance">Saldo</label>
            <input type="number" class="form-control" id="balance" name="balance" value="<?php echo $user['balance']; ?>" step="0.01" required>
        </div>
        <div class="form-group">
            <label for="rol">Rol</label>
            <select class="form-control" id="rol" name="rol" required>
                <option value="student" <?php if ($user['role'] == 'student') echo 'selected'; ?>>Estudiante</option>
                <option value="admin" <?php if ($user['role'] == 'admin') echo 'selected'; ?>>Administrador</option>
            </select>
        </div>
        <div class="form-group">
            <label for="nueva_contrasena">Nueva Contraseña (dejar en blanco para no cambiar)</label>
            <input type="password" class="form-control" id="nueva_contrasena" name="nueva_contrasena">
        </div>
        <button type="submit" class="btn btn-primary">Guardar Cambios</button>
        <button class="btn btn-secondary" onclick="history.back()">Volver Atrás</button>
    </form>
</div>

<?php include('includes/footer.php'); ?>
