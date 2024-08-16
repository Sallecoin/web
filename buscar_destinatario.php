<?php
include('db.php');

$nombre = isset($_GET['nombre']) ? $_GET['nombre'] : '';
$curso = isset($_GET['curso']) ? $_GET['curso'] : '';

// Construir la consulta SQL dinámicamente según los filtros seleccionados
$clausulas = [];
$params = [];

if ($nombre) {
    $clausulas[] = "name LIKE ?";
    $params[] = "%$nombre%";
}

if ($curso) {
    $clausulas[] = "course = ?";
    $params[] = $curso;
}

$sql = "SELECT id, name, rut, course FROM users WHERE role = 'student'";
if (count($clausulas) > 0) {
    $sql .= " AND " . implode(' AND ', $clausulas);
}

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$destinatarios = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Devolver los resultados como JSON
echo json_encode($destinatarios);
?>
