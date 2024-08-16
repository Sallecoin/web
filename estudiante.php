<?php
include('includes/header.php');
include('db.php');
session_start();

// Verifica si el usuario está logueado como estudiante
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'student') {
    header("Location: login.php");
    exit();
}

// Obtén el ID del estudiante actual
$student_id = $_SESSION['user_id'];

// Obtiene los detalles del estudiante
$stmt = $pdo->prepare("SELECT name, lastname, balance FROM users WHERE id = ?");
$stmt->execute([$student_id]);
$student = $stmt->fetch();

// Obtén el historial de transacciones del estudiante
$stmt = $pdo->prepare("
    SELECT 
        t.transaction_date, 
        s.name AS sender_name, 
        s.course AS sender_course, 
        r.name AS receiver_name, 
        r.course AS receiver_course, 
        t.amount 
    FROM 
        transactions t
    LEFT JOIN users s ON t.sender_id = s.id
    LEFT JOIN users r ON t.receiver_id = r.id
    WHERE t.sender_id = ? OR t.receiver_id = ?
    ORDER BY t.transaction_date DESC
");
$stmt->execute([$student_id, $student_id]);
$transactions = $stmt->fetchAll();
?>

<div class="container mt-5">
    <h2>Bienvenido, <?php echo htmlspecialchars($student['name'] . ' ' . $student['lastname']); ?></h2>
    <p>Tu saldo de Sallecoin: <strong><?php echo number_format($student['balance'], 2); ?></strong></p>

    <h3>Historial de Transacciones</h3>
    <table class="table table-striped">
        <thead>
            <tr>
                <th>Fecha</th>
                <th>Enviado a</th>
                <th>Recibido de</th>
                <th>Monto</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($transactions as $transaction): ?>
            <tr>
                <td><?php echo $transaction['transaction_date']; ?></td>
                <td>
                    <?php 
                    if ($transaction['sender_name']) {
                        echo htmlspecialchars($transaction['sender_name'] . ' (' . $transaction['sender_course'] . ')');
                    } else {
                        echo "N/A";
                    }
                    ?>
                </td>
                <td>
                    <?php 
                    if ($transaction['receiver_name']) {
                        echo htmlspecialchars($transaction['receiver_name'] . ' (' . $transaction['receiver_course'] . ')');
                    } else {
                        echo "N/A";
                    }
                    ?>
                </td>
                <td><?php echo number_format($transaction['amount'], 2); ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <a href="transaccion.php" class="btn btn-primary">Realizar Transacción</a>
    <a href="logout.php" class="btn btn-danger">Cerrar Sesión</a>
    <button class="btn btn-secondary" onclick="history.back()">Volver Atrás</button>
</div>

<?php include('includes/footer.php'); ?>
