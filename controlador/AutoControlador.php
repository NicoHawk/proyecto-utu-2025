<?php
header('Content-Type: application/json');

$servername = "localhost";
$username = "root";
$password = "root";
$dbname = "gestion_db"; 

session_start();
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    echo json_encode(['exito' => false, 'error' => 'Error de conexión']);
    exit;
}

// Detectar si la petición es JSON o POST tradicional
if ($_SERVER['CONTENT_TYPE'] === 'application/json') {
    $data = json_decode(file_get_contents('php://input'), true);
} else {
    $data = $_POST;
}

$accion = isset($data['accion']) ? $data['accion'] : '';


switch ($accion) {
    case 'agregar':
        if (!isset($_SESSION['usuario'])) {
            echo json_encode(['exito' => false, 'error' => 'No autenticado']);
            break;
        }
        $usuario = $_SESSION['usuario'];
        $modelo = $data['modelo'];
        $marca = $data['marca'];
        $conector = $data['conector'];
        $autonomia = $data['autonomia'];
        $anio = $data['anio'];

        $stmt = $conn->prepare("INSERT INTO autos (usuario, modelo, marca, conector, autonomia, anio) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssii", $usuario, $modelo, $marca, $conector, $autonomia, $anio);

        if ($stmt->execute()) {
            echo json_encode(['exito' => true]);
        } else {
            echo json_encode(['exito' => false, 'error' => $stmt->error]);
        }
        $stmt->close();
        break;

    case 'editar':
        if (!isset($_SESSION['usuario'])) {
            echo json_encode(['exito' => false, 'error' => 'No autenticado']);
            break;
        }
        $usuario = $_SESSION['usuario'];
        $id = $data['id'];
        $modelo = $data['modelo'];
        $marca = $data['marca'];
        $conector = $data['conector'];
        $autonomia = $data['autonomia'];
        $anio = $data['anio'];

        $stmt = $conn->prepare("UPDATE autos SET modelo=?, marca=?, conector=?, autonomia=?, anio=? WHERE id=? AND usuario=?");
        $stmt->bind_param("sssiiis", $modelo, $marca, $conector, $autonomia, $anio, $id, $usuario);

        if ($stmt->execute()) {
            echo json_encode(['exito' => true]);
        } else {
            echo json_encode(['exito' => false, 'error' => $stmt->error]);
        }
        $stmt->close();
        break;

    case 'eliminar':
        if (!isset($_SESSION['usuario'])) {
            echo json_encode(['exito' => false, 'error' => 'No autenticado']);
            break;
        }
        $usuario = $_SESSION['usuario'];
        $id = $data['id'];
        $stmt = $conn->prepare("DELETE FROM autos WHERE id=? AND usuario=?");
        $stmt->bind_param("is", $id, $usuario);

        if ($stmt->execute()) {
            echo json_encode(['exito' => true]);
        } else {
            echo json_encode(['exito' => false, 'error' => $stmt->error]);
        }
        $stmt->close();
        break;

    case 'listar':
        if (!isset($_SESSION['usuario'])) {
            echo json_encode([]);
            break;
        }
        $usuario = $_SESSION['usuario'];
        $stmt = $conn->prepare("SELECT * FROM autos WHERE usuario=? ORDER BY id DESC");
        $stmt->bind_param("s", $usuario);
        $stmt->execute();
        $result = $stmt->get_result();
        $autos = [];
        while($row = $result->fetch_assoc()) {
            $autos[] = $row;
        }
        echo json_encode($autos);
        $stmt->close();
        break;

    default:
        echo json_encode(['exito' => false, 'error' => 'Acción no válida']);
        break;
}

$conn->close();
?>