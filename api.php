<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Include file yang diperlukan
include_once 'db.php';
include_once 'auth.php';

// Instansiasi Database
$database = new Database();
$db = $database->getConnection();

// Instansiasi Auth
$auth = new Auth($db);

// Validasi token
$auth_result = $auth->validateToken();
if(!$auth_result['status']) {
    http_response_code(401);
    echo json_encode(array("status" => false, "message" => $auth_result['message']));
    exit;
}

// Ambil Request Method dan endpoint
$method = $_SERVER['REQUEST_METHOD'];
$request_uri = $_SERVER['REQUEST_URI'];

// Extract ID jika ada di URL
$id = null;
if(preg_match('/\/api\/mahasiswa\/(\d+)/', $request_uri, $matches)) {
    $id = $matches[1];
}

// Handle request berdasarkan method
switch($method) {
    case 'GET':
        if($id) {
            getById($db, $id);
        } else {
            getAll($db);
        }
        break;
    
    case 'POST':
        create($db);
        break;
    
    case 'PUT':
        if($id) {
            update($db, $id);
        } else {
            http_response_code(400);
            echo json_encode(array("status" => false, "message" => "ID mahasiswa diperlukan"));
        }
        break;
    
    case 'DELETE':
        if($id) {
            delete($db, $id);
        } else {
            http_response_code(400);
            echo json_encode(array("status" => false, "message" => "ID mahasiswa diperlukan"));
        }
        break;
    
    default:
        http_response_code(405);
        echo json_encode(array("status" => false, "message" => "Method tidak diizinkan"));
        break;
}

// Function untuk mendapatkan semua data mahasiswa
function getAll($db) {
    $query = "SELECT * FROM mahasiswa";
    $stmt = $db->prepare($query);
    $stmt->execute();
    
    $mahasiswa = array();
    $mahasiswa["status"] = true;
    $mahasiswa["mahasiswa"] = array();
    
    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        extract($row);
        
        $mahasiswa_item = array(
            "id" => $id,
            "nim" => $nim,
            "name" => $name
        );
        
        array_push($mahasiswa["mahasiswa"], $mahasiswa_item);
    }
    
    http_response_code(200);
    echo json_encode($mahasiswa);
}

// Function untuk mendapatkan data mahasiswa berdasarkan ID
function getById($db, $id) {
    $query = "SELECT * FROM mahasiswa WHERE id = ?";
    $stmt = $db->prepare($query);
    $stmt->execute([$id]);
    
    if($stmt->rowCount() > 0) {
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        extract($row);
        
        $mahasiswa = array(
            "status" => true,
            "mahasiswa" => array(
                "id" => $id,
                "nim" => $nim,
                "name" => $name
            )
        );
        
        http_response_code(200);
        echo json_encode($mahasiswa);
    } else {
        http_response_code(404);
        echo json_encode(array("status" => false, "message" => "Mahasiswa tidak ditemukan"));
    }
}

// Function untuk membuat data mahasiswa baru
function create($db) {
    $data = json_decode(file_get_contents("php://input"));
    
    if(!empty($data->nim) && !empty($data->name)) {
        $query = "INSERT INTO mahasiswa (nim, name) VALUES (?, ?)";
        $stmt = $db->prepare($query);
        
        if($stmt->execute([$data->nim, $data->name])) {
            http_response_code(201);
            echo json_encode(array(
                "status" => true,
                "message" => "Mahasiswa berhasil ditambahkan",
                "id" => $db->lastInsertId()
            ));
        } else {
            http_response_code(500);
            echo json_encode(array("status" => false, "message" => "Gagal menambahkan mahasiswa"));
        }
    } else {
        http_response_code(400);
        echo json_encode(array("status" => false, "message" => "NIM dan nama diperlukan"));
    }
}

// Function untuk mengupdate data mahasiswa
function update($db, $id) {
    $data = json_decode(file_get_contents("php://input"));
    
    if(!empty($data->nim) && !empty($data->name)) {
        // Cek apakah mahasiswa ada
        $check_query = "SELECT id FROM mahasiswa WHERE id = ?";
        $check_stmt = $db->prepare($check_query);
        $check_stmt->execute([$id]);
        
        if($check_stmt->rowCount() > 0) {
            $query = "UPDATE mahasiswa SET nim = ?, name = ? WHERE id = ?";
            $stmt = $db->prepare($query);
            
            if($stmt->execute([$data->nim, $data->name, $id])) {
                http_response_code(200);
                echo json_encode(array(
                    "status" => true,
                    "message" => "Mahasiswa berhasil diupdate"
                ));
            } else {
                http_response_code(500);
                echo json_encode(array("status" => false, "message" => "Gagal mengupdate mahasiswa"));
            }
        } else {
            http_response_code(404);
            echo json_encode(array("status" => false, "message" => "Mahasiswa tidak ditemukan"));
        }
    } else {
        http_response_code(400);
        echo json_encode(array("status" => false, "message" => "NIM dan nama diperlukan"));
    }
}

// Function untuk menghapus data mahasiswa
function delete($db, $id) {
    // Cek apakah mahasiswa ada
    $check_query = "SELECT id FROM mahasiswa WHERE id = ?";
    $check_stmt = $db->prepare($check_query);
    $check_stmt->execute([$id]);
    
    if($check_stmt->rowCount() > 0) {
        $query = "DELETE FROM mahasiswa WHERE id = ?";
        $stmt = $db->prepare($query);
        
        if($stmt->execute([$id])) {
            http_response_code(200);
            echo json_encode(array(
                "status" => true,
                "message" => "Mahasiswa berhasil dihapus"
            ));
        } else {
            http_response_code(500);
            echo json_encode(array("status" => false, "message" => "Gagal menghapus mahasiswa"));
        }
    } else {
        http_response_code(404);
        echo json_encode(array("status" => false, "message" => "Mahasiswa tidak ditemukan"));
    }
}
?>