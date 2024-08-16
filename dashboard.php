<?php
include('includes/header.php');
include('db.php');
session_start();

// Verificar si el usuario está logueado como administrador
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Obtener estadísticas
$total_estudiantes = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'student'")->fetchColumn();
$total_admins = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'admin'")->fetchColumn();
$total_transacciones = $pdo->query("SELECT COUNT(*) FROM transactions")->fetchColumn();
$total_sallecoin = $pdo->query("SELECT SUM(balance) FROM users")->fetchColumn();

// Obtener últimas transacciones
$stmt = $pdo->query("SELECT t.transaction_date, s.name AS sender_name, r.name AS receiver_name, t.amount 
                    FROM transactions t
                    JOIN users s ON t.sender_id = s.id
                    JOIN users r ON t.receiver_id = r.id
                    ORDER BY t.transaction_date DESC LIMIT 5");
$ultimas_transacciones = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Obtener datos para gráficos (ejemplo para transacciones por mes)
$transacciones_por_mes = $pdo->query("
    SELECT MONTHNAME(transaction_date) AS mes, COUNT(*) AS total 
    FROM transactions 
    GROUP BY MONTH(transaction_date)
    ORDER BY MONTH(transaction_date)
")->fetchAll(PDO::FETCH_ASSOC);

$meses = [];
$totales = [];

foreach ($transacciones_por_mes as $fila) {
    $meses[] = $fila['mes'];
    $totales[] = $fila['total'];
}
?>

<div class="container mt-5">
    <h1 class="text-center">Dashboard de Administrador</h1>
    <div class="row mt-4">
        <div class="col-md-3">
            <div class="card text-white bg-primary mb-3">
                <div class="card-body">
                    <h5 class="card-title">Estudiantes</h5>
                    <p class="card-text"><?php echo $total_estudiantes; ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-secondary mb-3">
                <div class="card-body">
                    <h5 class="card-title">Administradores</h5>
                    <p class="card-text"><?php echo $total_admins; ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-success mb-3">
                <div class="card-body">
                    <h5 class="card-title">Transacciones</h5>
                    <p class="card-text"><?php echo $total_transacciones; ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card text-white bg-info mb-3">
                <div class="card-body">
                    <h5 class="card-title">Total Sallecoin</h5>
                    <p class="card-text"><?php echo number_format($total_sallecoin, 2); ?></p>
                </div>
            </div>
        </div>
    </div>

    <h3 class="mt-5">Últimas Transacciones</h3>
    <table class="table table-striped">
        <thead>
            <tr>
                <th>Fecha</th>
                <th>Remitente</th>
                <th>Destinatario</th>
                <th>Monto</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($ultimas_transacciones as $transaccion): ?>
            <tr>
                <td><?php echo $transaccion['transaction_date']; ?></td>
                <td><?php echo htmlspecialchars($transaccion['sender_name']); ?></td>
                <td><?php echo htmlspecialchars($transaccion['receiver_name']); ?></td>
                <td><?php echo number_format($transaccion['amount'], 2); ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <h3 class="mt-5">Gráficos</h3>
    <div class="row">
        <div class="col-md-6">
            <canvas id="transaccionesChart"></canvas>
        </div>
        <!-- Aquí puedes agregar más gráficos -->
    </div>
</div>
<div class="container mt-5">
    

    <div class="mt-4 text-right">
        <a href="generate_pdf.php" class="btn btn-danger">Descargar Reporte en PDF</a>
        <button class="btn btn-secondary" onclick="history.back()">Volver Atrás</button>
    </div>

    <!-- El resto del contenido del dashboard, como gráficos y tablas -->
</div>

        
        

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const ctx = document.getElementById('transaccionesChart').getContext('2d');
const transaccionesChart = new Chart(ctx, {
    type: 'bar',
    data: {
        labels: <?php echo json_encode($meses); ?>,
        datasets: [{
            label: 'Transacciones por Mes',
            data: <?php echo json_encode($totales); ?>,
            backgroundColor: 'rgba(54, 162, 235, 0.2)',
            borderColor: 'rgba(54, 162, 235, 1)',
            borderWidth: 1
        }]
    },
    options: {
        scales: {
            y: {
                beginAtZero: true
            }
        }
    }
});
</script>

<?php include('includes/footer.php'); ?>
