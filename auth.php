<?php
require_once 'vendor/autoload.php';
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;

class Auth {
    private $secret_key = "220411100089"; 
    private $db;
    private $table_name = "users";

    public function __construct($db) {
        $this->db = $db;
    }

    public function register($email, $password) {
        // Validasi input
        if(empty($email) || empty($password)) {
            return ["status" => false, "message" => "Email dan password diperlukan"];
        }

        // Cek apakah email sudah digunakan
        $query = "SELECT id FROM " . $this->table_name . " WHERE email = ?";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$email]);

        if($stmt->rowCount() > 0) {
            return ["status" => false, "message" => "Email sudah digunakan"];
        }

        // Hash password
        $hashed_password = password_hash($password, PASSWORD_BCRYPT);

        // Query untuk insert data
        $query = "INSERT INTO " . $this->table_name . " (email, password) VALUES (?, ?)";
        $stmt = $this->db->prepare($query);

        if($stmt->execute([$email, $hashed_password])) {
            return ["status" => true, "message" => "Registrasi berhasil"];
        }

        return ["status" => false, "message" => "Registrasi gagal"];
    }

    public function login($email, $password) {
        // Validasi input
        if(empty($email) || empty($password)) {
            return ["status" => false, "message" => "Email dan password diperlukan"];
        }

        // Cek apakah user ada
        $query = "SELECT id, email, password FROM " . $this->table_name . " WHERE email = ?";
        $stmt = $this->db->prepare($query);
        $stmt->execute([$email]);

        if($stmt->rowCount() > 0) {
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            $id = $row['id'];
            $hashed_password = $row['password'];

            // Verifikasi password
            if(password_verify($password, $hashed_password)) {
                // Generate token
                $token = $this->generateToken($id, $email);
                return [
                    "status" => true,
                    "message" => "Login berhasil",
                    "token" => $token
                ];
            }
        }

        return ["status" => false, "message" => "Email atau password salah"];
    }

    public function generateToken($user_id, $email) {
        $issued_at = time();
        $expiration = $issued_at + (60 * 60);
        $payload = [
            "iat" => $issued_at,
            "exp" => $expiration,
            "user_id" => $user_id,
            "email" => $email
        ];

        $jwt = JWT::encode($payload, $this->secret_key, 'HS256');
        return $jwt;
    }

    public function validateToken() {
        // Ambil header Authorization
        $headers = getallheaders();
        $auth_header = isset($headers['Authorization']) ? $headers['Authorization'] : '';

        // Cek apakah header berisi token
        if(empty($auth_header) || !preg_match('/Bearer\s(\S+)/', $auth_header, $matches)) {
            return ["status" => false, "message" => "Token diperlukan"];
        }

        $jwt = $matches[1];

        try {
            // Validasi token
            $decoded = JWT::decode($jwt, new Key($this->secret_key, 'HS256'));
            return ["status" => true, "user" => $decoded];
        } catch(ExpiredException $e) {
            return ["status" => false, "message" => "Token tidak valid atau kadaluarsa"];
        } catch(Exception $e) {
            return ["status" => false, "message" => "Token tidak valid"];
        }
    }
}
?>