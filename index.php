<?php
$request_uri = $_SERVER['REQUEST_URI'];

// Routing
if(preg_match('/\/api\/mahasiswa/', $request_uri)) {
    // Route ke API mahasiswa
    include_once 'api.php';
} elseif($request_uri == '/'){
    include_once 'index.html';
} elseif($request_uri == '/login') {
    // Route ke halaman login
    include_once 'login.php';
} elseif($request_uri == '/register') {
    // Route ke halaman register
    include_once 'register.php';
} else {
    http_response_code(404);
    // include_once '404.html';
    echo json_encode(array("status" => false, "message" => "Endpoint tidak ditemukan"));
}
?>