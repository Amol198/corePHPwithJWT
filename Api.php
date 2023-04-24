<?php
require __DIR__ . '/BaseController.php';

class Api extends BaseController
{

    function __construct()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Process the POST request here
        } else {
            http_response_code(405); // Method Not Allowed
            header('Allow: POST');
            echo "Only POST requests are allowed.";
            exit();
        }
    }

    public function registration()
    {
        $response = array();
        $errors = array();
        // Validate name
        if (! isset($_POST['name'])) {
            $errors[] = 'Name is required';
        } else if (! preg_match('/^[a-zA-Z\s]+$/', $_POST['name'])) {
            $errors[] = 'Name can only contain letters and spaces';
        }

        // Validate email
        if (! isset($_POST['email'])) {
            $errors[] = 'Email is required';
        } else if (! filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Email is invalid';
        }

        // Validate password
        if (! isset($_POST['password'])) {
            $errors[] = 'Password is required';
        } else {
            // Password validation criteria
            $uppercase = preg_match('@[A-Z]@', $_POST['password']);
            $lowercase = preg_match('@[a-z]@', $_POST['password']);
            $number = preg_match('@[0-9]@', $_POST['password']);
            $specialChars = preg_match('@[^\w]@', $_POST['password']);

            if (! $uppercase || ! $lowercase || ! $number || ! $specialChars || strlen($_POST['password']) < 8) {
                $errors[] = 'Password must be at least 8 characters long and contain at least one uppercase letter, one lowercase letter, one number, and one special character';
            }
        }

        // If there are errors, display them as JSON response
        if (! empty($errors)) {
            $response = array(
                "status" => "error",
                "message" => $errors
            );
            echo json_encode($response);
        } else {
            // Insert user data into database
            $conn = $this->db_connection();
            // Check if email exists

            $sql = "SELECT id from users where email='" . $_POST['email'] . "'";
            $result = mysqli_query($conn, $sql);
            if (mysqli_num_rows($result) > 0) {
                $errors[] = 'Email exists in database!';
                $response = array(
                    "status" => "error",
                    "message" => $errors
                );
            } else {
                // Insert user data into database

                $sql = "INSERT INTO users (name, email, password) VALUES ('" . $_POST['name'] . "', '" . $_POST['email'] . "', '" . md5($_POST['password']) . "')";

                if (mysqli_query($conn, $sql)) {
                    $last_id = $conn->insert_id;
                    $access_token = $this->getToken($last_id);
                    if (mysqli_query($conn, $sql)) {}
                    $response = array(
                        "status" => "success",
                        "message" => "Registration successful!",
                        "access_token" => $access_token
                    );
                } else {
                    $response = array(
                        "status" => "error",
                        "message" => mysqli_error($conn)
                    );
                    echo json_encode($response);
                }
            }
            echo json_encode($response);
        }
    }

    public function login()
    {
        $response = array();
        $errors = array();
        if (! isset($_POST['email']) || ! isset($_POST['password'])) {
            $errors[] = 'Email and Password are required';
        }
        if (! empty($errors)) {
            $response = array(
                "status" => "error",
                "message" => $errors
            );
            echo json_encode($response);
        } else {
            $conn = $this->db_connection();
            $sql = "SELECT * FROM users WHERE email='" . $_POST['email'] . "' AND password='" . md5($_POST['password']) . "'";

            // Execute query
            $result = mysqli_query($conn, $sql);

            // Check if query was successful
            if ($result === false) {
                $response = array(
                    'status' => 'error',
                    'message' => mysqli_error($conn)
                );
            } else {
                // Check if there are any rows returned
                if (mysqli_num_rows($result) > 0) {
                    $access_token = "";
                    while ($row = mysqli_fetch_assoc($result)) {
                        $access_token = $this->getToken($row['id']);
                    }
                    // User exists and credentials are correct

                    $response = array(
                        'status' => 'success',
                        'message' => 'Login successful',
                        "access_token" => $access_token
                    );
                } else {
                    // User does not exist or credentials are incorrect
                    $response = array(
                        'status' => 'error',
                        'message' => 'Invalid email or password'
                    );
                }
            }

            echo json_encode($response);
        }
    }

    public function create_task()
    {
        $access_token_data = $this->validateHeader($this->getAuthorizationHeader());
        $userId = $access_token_data->userId;
        if (! isset($_POST['subject'])) {
            $errors[] = 'Subject is required';
        }
        if (! isset($_POST['description'])) {
            $errors[] = 'Description is required';
        }
        if (! isset($_POST['start_date'])) {
            $errors[] = 'Start date is required';
        }
        if (! isset($_POST['due_date'])) {
            $errors[] = 'Due date is required';
        }

        if (! isset($_POST['status'])) {
            $errors[] = 'Status is required';
        } else {
            $status_list = array(
                'New',
                'Incomplete',
                'Complete'
            );
            if (! in_array($_POST['status'], $status_list)) {
                $errors[] = 'Please enter valid status';
            }
        }
        if (! isset($_POST['priority'])) {
            $errors[] = 'priority is required';
        } else {
            $priorities_list = array(
                'High',
                'Medium',
                'Low'
            );
            if (! in_array($_POST['priority'], $priorities_list)) {
                $errors[] = 'Please enter valid priority';
            }
            // if we need notes validation then we can uncomment below code, means user have to add atleast 1 note
            /*
             * if (! isset($_POST['notes']) || ! is_array($_POST['notes'])) {
             * $errors[] = 'Please add notes';
             * } else {
             * for ($i = 0; $i < count($_POST['notes']); $i ++) {
             * if (empty($user['subject']) || empty($user['note'])) {
             * $errors[] = 'Please enter Subject and Note';
             * break;
             * }
             * }
             * }
             */
        }

        if (! empty($errors)) {
            $response = array(
                "status" => "error",
                "message" => $errors
            );
            echo json_encode($response);
        } else {
            $conn = $this->db_connection();
            if (isset($_POST['notes'])) {
                $total_notes = count($_POST['notes']);
            } else {
                $total_notes = 0;
                $_POST['notes'] = array();
            }
            $sql = "INSERT INTO task (subject,description,start_date,due_date,total_notes,status,priority,user_id) 
VALUES ('" . $_POST['subject'] . "', '" . $_POST['description'] . "', '" . $_POST['start_date'] . "', '" . $_POST['due_date'] . "', '" . $total_notes . "', '" . $_POST['status'] . "', '" . $_POST['priority'] . "','" . $userId . "')";
            if (mysqli_query($conn, $sql)) {

                $last_id = $conn->insert_id;

                for ($i = 0; $i < count($_POST['notes']); $i ++) {
                    $fileName = "";
                    // check if is there any files into it
                    if (isset($_FILES['notes'])) {
                        // check if file names and all things are set
                        print_r($_FILES);
                        exit();
                        if (isset($_FILES['notes']['name'][$i]['upload_file'])) {

                            $fileName = json_encode($_FILES['notes']['name'][$i]['upload_file']);
                            // Here we have Tasks first then multiple notes into it and in each note we have multiple files.
                            // thats why I have addes second for loop
                            for ($k = 0; $k < count($_FILES['notes']['name'][$i]['upload_file']); $k ++) {

                                $errors = array();
                                $file_name = $_FILES['notes']['name'][$i]['upload_file'][$k];
                                $file_size = $_FILES['notes']['size'][$i]['upload_file'][$k];
                                $file_tmp = $_FILES['notes']['tmp_name'][$i]['upload_file'][$k];
                                $file_type = $_FILES['notes']['type'][$i]['upload_file'][$k];
                                $tmp = explode('.', $_FILES['notes']['name'][$i]['upload_file'][$k]);
                                $file_ext = end($tmp);
                                if ($_FILES['notes']['error'][$i]['upload_file'][$k] == 0) {
                                    // after image upload we are putting all files in images folder
                                    move_uploaded_file($file_tmp, "images/" . $file_name);
                                }
                            }
                        }
                    }
                    // update all notes as well as files into it
                    $sql2 = "INSERT INTO notes (task_id, subject, note,attachment) VALUES ('" . $last_id . "', '" . $_POST['notes'][$i]['subject'] . "', '" . $_POST['notes'][$i]['note'] . "', '" . $fileName . "')";
                    if (mysqli_query($conn, $sql2)) {} else {

                        $response = array(
                            "status" => "error",
                            "message" => mysqli_error($conn)
                        );
                    }
                }
                $response = array(
                    "status" => "success",
                    "message" => "Task created successfully!"
                );
            } else {

                $response = array(
                    "status" => "error",
                    "message" => mysqli_error($conn)
                );
            }
            echo json_encode($response);
        }
    }

    public function reports()
    {
        $access_token_data = $this->validateHeader($this->getAuthorizationHeader());
        $status = "";
        $due_date = "";

        // by default ORDER BY high , We have used ENUM "High first" that's why in ascending format
        $priority = " ORDER BY priority ASC,total_notes DESC";
        $minimum_one_note = "";
        if (isset($_POST['minimum_one_note']))
            $minimum_one_note = " and total_notes>0";

        if (isset($_POST['status']))
            $status = " and status='" . $_POST['status'] . "'";

        if (isset($_POST['due_date']))
            $due_date = " and due_date='" . $_POST['due_date'] . "'";

        if (isset($_POST['priority']) && $_POST['priority'] == "Low")
            $priority = " ORDER BY priority DESC,total_notes DESC";

        $pdo = new PDO('mysql:host=localhost;dbname=tasks', 'root', '');

        $stmt = $pdo->query("SELECT * from task  where id>0 " . $status . $due_date . $minimum_one_note . $priority . "");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);

        for ($i = 0; $i < count($rows); $i ++) {
            $stmt = $pdo->query("SELECT * from notes  where task_id=" . $rows[$i]['id'] . "");
            $rows[$i]['notes'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

        $row = json_encode($rows);
        $row = str_replace('"[\"', '["', $row);
        $row = str_replace('\"]"', '"]', $row);
        $row = str_replace('\"', '"', $row);

        echo $row;
    }
}
?>