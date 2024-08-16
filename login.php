<?php
include('includes/header.php');
include('db.php');
session_start();

// Manejar inicio de sesión
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $rut = $_POST['rut'];
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE rut = ?");
    $stmt->execute([$rut]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $user['role'];
        if ($user['role'] === 'admin') {
            header("Location: administrador.php");
        } else {
            header("Location: estudiante.php");
        }
        exit();
    } else {
        echo "<div class='alert alert-danger'>RUT o contraseña incorrectos.</div>";
    }
}
?>

<div class="container mt-5">
    <h2 class="text-center">Iniciar Sesión</h2>
    <form action="login.php" method="POST">
        <div class="form-group">
            <label for="rut">RUT</label>
            <input type="text" class="form-control" id="rut" name="rut" required>
        </div>
        <div class="form-group">
            <label for="password">Contraseña</label>
            <input type="password" class="form-control" id="password" name="password" required>
        </div>
        <button type="submit" class="btn btn-success">Iniciar Sesión</button>
    </form>
</div>
<script>
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
</script>

<?php include('includes/footer.php'); ?>
