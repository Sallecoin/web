<?php
include('includes/header.php');
include('db.php');
session_start();

// Verifica si el usuario está logueado
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Obtener la lista de cursos para el desplegable
$stmt = $pdo->query("SELECT DISTINCT course FROM users WHERE role = 'student'");
$cursos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Manejar la transacción
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $destinatario_id = $_POST['destinatario'];
    $monto = $_POST['monto'];

    // Obtener el balance actual del remitente
    $stmt = $pdo->prepare("SELECT balance FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $remitente_balance = $stmt->fetchColumn();

    if ($remitente_balance >= $monto) {
        // Iniciar transacción
        $pdo->beginTransaction();

        try {
            // Restar saldo al remitente
            $stmt = $pdo->prepare("UPDATE users SET balance = balance - ? WHERE id = ?");
            $stmt->execute([$monto, $_SESSION['user_id']]);

            // Sumar saldo al destinatario
            $stmt = $pdo->prepare("UPDATE users SET balance = balance + ? WHERE id = ?");
            $stmt->execute([$monto, $destinatario_id]);

            // Registrar la transacción
            $stmt = $pdo->prepare("INSERT INTO transactions (sender_id, receiver_id, amount) VALUES (?, ?, ?)");
            $stmt->execute([$_SESSION['user_id'], $destinatario_id, $monto]);

            // Confirmar transacción
            $pdo->commit();

            echo "<div class='alert alert-success'>Transacción realizada con éxito.</div>";
        } catch (Exception $e) {
            // Revertir transacción en caso de error
            $pdo->rollBack();
            echo "<div class='alert alert-danger'>Error en la transacción: " . $e->getMessage() . "</div>";
        }
    } else {
        echo "<div class='alert alert-danger'>Saldo insuficiente.</div>";
    }
}
?>

<div class="container mt-5">
    <h2>Realizar Transacción</h2>
    <form action="transaccion.php" method="POST">
        <div class="form-group">
            <label>Buscar por Nombre y Curso</label>
            <div class="form-row">
                <div class="col">
                    <input type="text" class="form-control" id="buscar" placeholder="Escriba el nombre">
                </div>
                <div class="col">
                    <select class="form-control" id="curso">
                        <option value="">Seleccione un curso</option>
                        <?php foreach ($cursos as $curso): ?>
                            <option value="<?php echo $curso['course']; ?>"><?php echo $curso['course']; ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col">
                    <button type="button" class="btn btn-primary" id="btnBuscar">Buscar</button>
                </div>
            </div>
        </div>

        <div id="resultado-busqueda" class="mt-3">
            <!-- Aquí aparecerán los resultados filtrados en una tabla -->
        </div>

        <div class="form-group mt-3">
            <label for="monto">Monto</label>
            <input type="number" class="form-control" id="monto" name="monto" required>
        </div>
        <button type="submit" class="btn btn-primary">Enviar</button>
        <button class="btn btn-secondary" onclick="history.back()">Volver Atrás</button>
    </form>
</div>

<script>
document.getElementById('btnBuscar').addEventListener('click', function() {
    const nombre = document.getElementById('buscar').value;
    const curso = document.getElementById('curso').value;

    if (nombre.length > 0 || curso.length > 0) {
        fetch('buscar_destinatario.php?nombre=' + nombre + '&curso=' + curso)
            .then(response => response.json())
            .then(data => {
                const resultados = document.getElementById('resultado-busqueda');
                if (data.length > 0) {
                    resultados.innerHTML = `
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>RUT</th>
                                    <th>Nombre</th>
                                    <th>Curso</th>
                                    <th>Seleccionar</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${data.map(destinatario => `
                                    <tr>
                                        <td>${destinatario.rut}</td>
                                        <td>${destinatario.name}</td>
                                        <td>${destinatario.course}</td>
                                        <td>
                                            <input type="radio" name="destinatario" value="${destinatario.id}" required>
                                        </td>
                                    </tr>
                                `).join('')}
                            </tbody>
                        </table>
                    `;
                } else {
                    resultados.innerHTML = '<p>No se encontraron resultados.</p>';
                }
            })
            .catch(error => console.error('Error:', error));
    } else {
        document.getElementById('resultado-busqueda').innerHTML = '<p>Por favor, ingrese un nombre o seleccione un curso.</p>';
    }
});
</script>

<?php include('includes/footer.php'); ?>
