<?php
include('includes/header.php');
include('db.php');

$errores = [];

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Validar nombre y apellido (solo letras)
    if (!preg_match("/^[a-zA-Z]+$/", $_POST['nombre'])) {
        $errores[] = "El nombre solo debe contener letras.";
    }

    if (!preg_match("/^[a-zA-Z]+$/", $_POST['apellido'])) {
        $errores[] = "El apellido solo debe contener letras.";
    }

    // Validar que las contraseñas coincidan
    if ($_POST['password'] !== $_POST['password_confirm']) {
        $errores[] = "Las contraseñas no coinciden.";
    }

    // Si no hay errores, continuar con el registro
    if (empty($errores)) {
        $nombre = $_POST['nombre'];
        $apellido = $_POST['apellido'];
        $rut = $_POST['rut'];
        $curso = $_POST['curso'];
        $anio = $_POST['anio'];
        $password = password_hash($_POST['password'], PASSWORD_BCRYPT);
        $saldo_inicial = 30.00; // Saldo inicial de 30 Sallecoin

        // Verificar si el RUT ya está registrado
        $stmt = $pdo->prepare("SELECT * FROM users WHERE rut = ?");
        $stmt->execute([$rut]);
        if ($stmt->rowCount() > 0) {
            $errores[] = "El RUT ya está registrado.";
        } else {
            $stmt = $pdo->prepare("INSERT INTO users (name, lastname, rut, course, year, balance, password) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$nombre, $apellido, $rut, $curso, $anio, $saldo_inicial, $password]);
            echo "<div class='alert alert-success'>Registro exitoso. Ahora puedes <a href='login.php'>iniciar sesión</a>.</div>";
        }
    }
}
?>

<div class="container mt-5">
    <h2 class="text-center">Registro de Usuario</h2>

    <?php if (!empty($errores)): ?>
        <div class="alert alert-danger">
            <ul>
                <?php foreach ($errores as $error): ?>
                    <li><?php echo $error; ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form action="registro.php" method="POST">
        <div class="form-group">
            <label for="nombre">Nombre</label>
            <input type="text" class="form-control" id="nombre" name="nombre" required>
        </div>
        <div class="form-group">
            <label for="apellido">Apellido</label>
            <input type="text" class="form-control" id="apellido" name="apellido" required>
        </div>
        <div class="form-group">
            <label for="rut">RUT</label>
            <input type="text" class="form-control" id="rut" name="rut" required>
        </div>
        <div class="form-group">
            <label for="curso">Curso</label>
            <select class="form-control" id="curso" name="curso" required>
                <option value="">Seleccione un curso</option>
                <option value="7°">7°</option>
                <option value="8°">8°</option>
                <option value="1°A">1°A</option>
                <option value="1°B">1°B</option>
                <option value="1°C">1°C</option>
                <option value="2°A">2°A</option>
                <option value="2°B">2°B</option>
                <option value="2°C">2°C</option>
            </select>
        </div>
        <div class="form-group">
            <label for="anio">Año</label>
            <input type="number" class="form-control" id="anio" name="anio" value="<?php echo date('Y'); ?>" required>
        </div>
        <div class="form-group">
            <label for="password">Contraseña</label>
            <input type="password" class="form-control" id="password" name="password" required>
        </div>
        <div class="form-group">
            <label for="password_confirm">Confirmar Contraseña</label>
            <input type="password" class="form-control" id="password_confirm" name="password_confirm" required>
        </div>
        <button type="submit" class="btn btn-primary">Registrarse</button>
        <button class="btn btn-secondary" onclick="history.back()">Volver Atrás</button>
    </form>
</div>

<script>
// Formatear RUT automáticamente
document.getElementById('rut').addEventListener('input', function() {
    let rut = this.value.replace(/\D/g, '');
    let formattedRut = '';
    if (rut.length > 1) {
        formattedRut = rut.slice(0, -1).replace(/\B(?=(\d{3})+(?!\d))/g, '.') + '-' + rut.slice(-1);
    } else {
        formattedRut = rut;
    }
    this.value = formattedRut;
});

// Validar que los campos de nombre y apellido solo contengan letras
document.getElementById('nombre').addEventListener('input', function() {
    this.value = this.value.replace(/[^a-zA-ZáéíóúÁÉÍÓÚñÑ\s]/g, '');
});
document.getElementById('apellido').addEventListener('input', function() {
    this.value = this.value.replace(/[^a-zA-ZáéíóúÁÉÍÓÚñÑ\s]/g, '');
});
</script>

<?php include('includes/footer.php'); ?>
