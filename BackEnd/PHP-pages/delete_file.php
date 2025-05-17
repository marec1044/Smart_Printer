<?php
// Set appropriate headers
header('Content-Type: application/json');

// Get JSON data from request
$jsonData = file_get_contents('php://input');
$data = json_decode($jsonData, true);

// Check if data is valid
if (!$data || !isset($data['file_path']) || !isset($data['file_name'])) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid data provided'
    ]);
    exit;
}

// Extract file information
$filePath = $data['file_path'];
$fileName = $data['file_name'];
$isTemporary = isset($data['is_temporary']) ? $data['is_temporary'] : false;

// Safety check - only allow deletion from specific directories
$allowedDirs = [
    '../uploads/temp/',
    '../uploads/'
];

$validPath = false;
foreach ($allowedDirs as $dir) {
    if (strpos($filePath, $dir) === 0) {
        $validPath = true;
        break;
    }
}

if (!$validPath) {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid file path'
    ]);
    exit;
}

// Build the full path
$fullPath = $filePath . $fileName;

// Make sure the file exists
if (!file_exists($fullPath)) {
    echo json_encode([
        'success' => false,
        'message' => 'File not found'
    ]);
    exit;
}

// Delete the file
if (unlink($fullPath)) {
    echo json_encode([
        'success' => true,
        'message' => 'File deleted successfully'
    ]);
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Failed to delete file'
    ]);
}
?>