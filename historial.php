<?php
include('includes/header.php');
include('db.php');
session_start();

// Verifica si el usuario está logueado
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

// Obtén el ID del estudiante seleccionado
if (isset($_GET['id'])) {
    $student_id = $_GET['id'];

    // Obtén los detalles del estudiante
    $stmt = $pdo->prepare("SELECT name, lastname, balance FROM users WHERE id = ?");
    $stmt->execute([$student_id]);
    $student = $stmt->fetch();

    if (!$student) {
        echo "Estudiante no encontrado.";
        exit();
    }

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
} else {
    echo "ID de estudiante no proporcionado.";
    exit();
}
?>

<div class="container mt-5">
    <h2>Historial de Transacciones de <?php echo htmlspecialchars($student['name'] . ' ' . $student['lastname']); ?></h2>
    <p>Saldo de Sallecoin: <strong><?php echo number_format($student['balance'], 2); ?></strong></p>

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

    <button class="btn btn-secondary" onclick="history.back()">Volver Atrás</button>

</div>

<?php include('includes/footer.php'); ?>
