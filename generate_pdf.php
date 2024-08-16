<?php
require('fpdf186/fpdf.php');
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

// Crear nuevo PDF usando FPDF
$pdf = new FPDF();
$pdf->AddPage();

// Título del PDF
$pdf->SetFont('Arial', 'B', 16);
$pdf->Cell(0, 10, 'Reporte del Dashboard de Sallecoin', 0, 1, 'C');
$pdf->Ln(10);

// Agregar las estadísticas
$pdf->SetFont('Arial', '', 12);
$pdf->Cell(0, 10, 'Estadisticas', 0, 1, 'L');
$pdf->Ln(5);
$pdf->Cell(0, 10, 'Total Estudiantes: ' . $total_estudiantes, 0, 1, 'L');
$pdf->Cell(0, 10, 'Total Administradores: ' . $total_admins, 0, 1, 'L');
$pdf->Cell(0, 10, 'Total Transacciones: ' . $total_transacciones, 0, 1, 'L');
$pdf->Cell(0, 10, 'Total Sallecoin en Circulacion: ' . number_format($total_sallecoin, 2), 0, 1, 'L');
$pdf->Ln(10);

// Agregar las últimas transacciones
$pdf->Cell(0, 10, 'Ultimas Transacciones', 0, 1, 'L');
$pdf->Ln(5);

$pdf->SetFont('Arial', 'B', 12);
$pdf->Cell(50, 10, 'Fecha', 1);
$pdf->Cell(50, 10, 'Remitente', 1);
$pdf->Cell(50, 10, 'Destinatario', 1);
$pdf->Cell(40, 10, 'Monto', 1);
$pdf->Ln();

$pdf->SetFont('Arial', '', 12);
foreach ($ultimas_transacciones as $transaccion) {
    $pdf->Cell(50, 10, $transaccion['transaction_date'], 1);
    $pdf->Cell(50, 10, htmlspecialchars($transaccion['sender_name']), 1);
    $pdf->Cell(50, 10, htmlspecialchars($transaccion['receiver_name']), 1);
    $pdf->Cell(40, 10, number_format($transaccion['amount'], 2), 1);
    $pdf->Ln();
}

// Salida del PDF
$pdf->Output('D', 'reporte_dashboard.pdf');
?>
