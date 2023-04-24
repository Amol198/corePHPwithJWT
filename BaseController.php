<?php
require __DIR__ . '/vendor/autoload.php';
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class BaseController
{

    public function throwError($code, $message)
    {
        header("content-type: application/json");
        $errorMsg = json_encode([
            'error' => [
                'status' => $code,
                'message' => $message
            ]
        ]);
        echo $errorMsg;
        exit();
    }

    /**
     * Get hearder Authorization
     */
    public function getAuthorizationHeader()
    {
        $headers = null;
        if (isset($_SERVER['Authorization'])) {
            $headers = trim($_SERVER["Authorization"]);
        } else if (isset($_SERVER['HTTP_AUTHORIZATION'])) { // Nginx or fast CGI
            $headers = trim($_SERVER["HTTP_AUTHORIZATION"]);
        } elseif (function_exists('apache_request_headers')) {
            $requestHeaders = apache_request_headers();
            // Server-side fix for bug in old Android versions (a nice side-effect of this fix means we don't care about capitalization for Authorization)
            $requestHeaders = array_combine(array_map('ucwords', array_keys($requestHeaders)), array_values($requestHeaders));
            if (isset($requestHeaders['Authorization'])) {
                $headers = trim($requestHeaders['Authorization']);
            }
        }
        
        return (isset(explode(" ",$headers)[1]))?explode(" ",$headers)[1]:$this->throwError("200","Token not found");
    }

    /**
     * get access token from header
     */
    public function getBearerToken()
    {
        $headers = $this->getAuthorizationHeader();
        // HEADER: Get the access token from the header
        if (! empty($headers)) {
            if (preg_match('/Bearer\s(\S+)/', $headers, $matches)) {
                return $matches[1];
            }
        }
        $this->throwError(ATHORIZATION_HEADER_NOT_FOUND, 'Access Token Not found');
    }

    function validateHeader($token)
    {
        try {
            $payload = JWT::decode($token, new Key("SECRET_KEY", 'HS256'));
        } catch (Exception $e) {
            // handle the error appropriately
            error_log('Error decoding JWT token: ' . $e->getMessage());
            // display a user-friendly error message

            $payload = 'There was an error processing your request. Please try again later.';
            $response = array(
                "status" => "error",
                "message" => $payload
            );
            echo json_encode($response);
            exit();
        }
        return $payload;
    }

    function getToken($id)
    {
        $paylod = [
            'iat' => time(),
            'iss' => 'localhost',
            'exp' => time() + (15 * 60),
            'userId' => $id
        ];

        $token = JWT::encode($paylod, "SECRET_KEY", 'HS256');
        return $token;
    }

    public function db_connection()
    {
        $host = 'localhost';
        $user = 'root';
        $pass = '';
        $dbname = 'tasks';
        $conn = mysqli_connect($host, $user, $pass, $dbname);
        return $conn;
    }
}
?>