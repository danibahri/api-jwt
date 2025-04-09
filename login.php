<?php
// Header untuk Cross-Origin Resource Sharing
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Include file yang diperlukan
include_once 'db.php';
include_once 'auth.php';

// Instansiasi Database
$database = new Database();
$db = $database->getConnection();

// Instansiasi Auth
$auth = new Auth($db);

// Ambil data dari request
$data = json_decode(file_get_contents("php://input"));

if(!empty($data->email) && !empty($data->password)) {
    $result = $auth->login($data->email, $data->password);
    
    // Mengembalikan response
    http_response_code($result['status'] ? 200 : 401);
    echo json_encode($result);
} else {
    // Response jika data tidak lengkap
    http_response_code(400);
    echo json_encode(array("status" => false, "message" => "Email dan password diperlukan"));
}
?>