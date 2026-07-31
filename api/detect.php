<?php
// api/detect.php
header('Content-Type: application/json');
require_once '../database/db.php';
start_secure_session();

if (!isset($_SESSION['user_id'])) {
    echo json_encode(["success" => false, "error" => "Unauthorized access validation context failed."]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(["success" => false, "error" => "Invalid POST request paradigm."]);
    exit;
}

if (!isset($_FILES['vehicle_image']) || $_FILES['vehicle_image']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(["success" => false, "error" => "File upload execution pipeline breakdown."]);
    exit;
}

$fileTmpPath = $_FILES['vehicle_image']['tmp_name'];
$fileName = $_FILES['vehicle_image']['name'];
$fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

$allowedExtensions = ['jpg', 'jpeg', 'png'];
if (!in_array($fileExtension, $allowedExtensions)) {
    echo json_encode(["success" => false, "error" => "Unsupported file type signature extensions."]);
    exit;
}

$uploadVehicleDir = '../uploads/vehicles/';
if(!is_dir($uploadVehicleDir)) {
    mkdir($uploadVehicleDir, 0755, true);
}

$newFileName = 'vehicle_' . uniqid('', true) . '.' . $fileExtension;
$dest_path = $uploadVehicleDir . $newFileName;

if(move_uploaded_file($fileTmpPath, $dest_path)) {
    $absoluteImagePath = realpath($dest_path);
    $pythonScriptPath = realpath('../python/detect_plate.py');

    $projectPython = realpath(__DIR__ . '/../.venv/Scripts/python.exe');
    if (!$projectPython) {
        echo json_encode([
            "success" => false,
            "error" => "Python runtime was not found in the project environment."
        ]);
        exit;
    }

    $command = escapeshellarg($projectPython) . " " . escapeshellarg($pythonScriptPath) . " " . escapeshellarg($absoluteImagePath);

    $descriptors = [
        0 => ["pipe", "r"],
        1 => ["pipe", "w"],
        2 => ["pipe", "w"],
    ];

    $process = proc_open($command, $descriptors, $pipes, null, [
        "PYTHONUNBUFFERED" => "1",
    ]);

    if (!is_resource($process)) {
        echo json_encode([
            "success" => false,
            "error" => "Could not start the ANPR detection process."
        ]);
        exit;
    }

    fclose($pipes[0]);
    $output = stream_get_contents($pipes[1]);
    fclose($pipes[1]);
    $stderr = stream_get_contents($pipes[2]);
    fclose($pipes[2]);
    $exitCode = proc_close($process);

    $responseObj = json_decode(trim($output), true);

    if ($exitCode === 0 && is_array($responseObj) && isset($responseObj['success'], $responseObj['plate_number'])) {
        $plateNum = sanitize($responseObj['plate_number']);
        $platePath = sanitize($responseObj['plate_path']);
        $dbVehiclePath = 'uploads/vehicles/' . $newFileName;
        
        $stmt = $pdo->prepare("INSERT INTO scans (user_id, vehicle_image_path, plate_image_path, plate_number) VALUES (?, ?, ?, ?)");
        $stmt->execute([$_SESSION['user_id'], $dbVehiclePath, $platePath, $plateNum]);
        
        echo json_encode([
            "success" => true,
            "plate_number" => $plateNum,
            "plate_path" => $platePath
        ]);
    } else {
        $errorMessage = trim($stderr);
        if ($errorMessage === '') {
            $errorMessage = trim($output);
        }

        echo json_encode([
            "success" => false, 
            "error" => $errorMessage !== '' ? $errorMessage : "ANPR detection failed to return a valid result."
        ]);
    }
} else {
    echo json_encode(["success" => false, "error" => "Error moving local validation structures onto system storage filesystems."]);
}
?>