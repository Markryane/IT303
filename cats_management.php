<?php
header('Content-Type: application/json');

// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "cats_management";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$response = array("method" => $_SERVER['REQUEST_METHOD']);

// Retrieve raw JSON data from request body
$data = file_get_contents('php://input',true);

// Decode JSON data
$decodedData = json_decode($data, true);

switch ($_SERVER['REQUEST_METHOD']) {
    case 'GET':
        // Read operation
        $sql = "SELECT * FROM dog";
        $result = $conn->query($sql);
        
        if ($result->num_rows > 0) {
            $cats = array();
            while ($row = $result->fetch_assoc()) {
                $cats[] = $row;
            }
            $response['data'] = $cats;
        } else {
            $response['data'] = [];
            $response['message'] = 'No cats found.';
        }
        break;

    case 'POST':
        // Create operation
        $name = $decodedData['name'] ?? '';
        $age = $decodedData['age'] ?? '';
        $color = $decodedData['color'] ?? '';
        
        if ($name && $age && $color) {
            $stmt = $conn->prepare("INSERT INTO dog (name, age, color) VALUES (?, ?, ?)");
            $stmt->bind_param("sis", $name, $age, $color);
            
            if ($stmt->execute()) {
                $response['status'] = 'success';
                $response['message'] = 'Cat added successfully.';
                $response['data'] = array(
                    'id' => $stmt->insert_id,
                    'name' => $name,
                    'age' => $age,
                    'color' => $color
                );
            } else {
                $response['status'] = 'error';
                $response['message'] = 'Failed to add cat.';
            }
            
            $stmt->close();
        } else {
            $response['status'] = 'error';
            $response['message'] = 'Missing required fields.';
        }
        break;

    case 'PUT':
        // Update operation
        $id = $decodedData['id'] ?? '';
        $name = $decodedData['name'] ?? '';
        $age = $decodedData['age'] ?? '';
        $color = $decodedData['color'] ?? '';
        
        if ($id && ($name || $age || $color)) {
            $sql = "UPDATE dog SET ";
            $params = array();
            $types = '';

            if ($name) {
                $sql .= "name = ?, ";
                $params[] = $name;
                $types .= 's';
            }
            if ($age) {
                $sql .= "age = ?, ";
                $params[] = $age;
                $types .= 's';
            }
            if ($color) {
                $sql .= "color = ?, ";
                $params[] = $color;
                $types .= 's';
            }

            $sql = rtrim($sql, ', ') . " WHERE id = ?";
            $params[] = $id;
            $types .= 's';

            $stmt = $conn->prepare($sql);
            $stmt->bind_param($types, ...$params);
            
            if ($stmt->execute()) {
                $response['status'] = 'success';
                $response['message'] = 'Cat updated successfully.';
            } else {
                $response['status'] = 'error';
                $response['message'] = 'Failed to update cat.';
            }
            
            $stmt->close();
        } else {
            $response['status'] = 'error';
            $response['message'] = 'Invalid ID or missing fields.';
        }
        break;

    case 'DELETE':
        // Delete operation
        $id = $decodedData['id'] ?? '';
        
        if ($id) {
            $stmt = $conn->prepare("DELETE FROM dog WHERE id = ?");
            $stmt->bind_param("i", $id);
            
            if ($stmt->execute()) {
                $response['status'] = 'success';
                $response['message'] = 'Cat deleted successfully.';
            } else {
                $response['status'] = 'error';
                $response['message'] = 'Failed to delete cat.';
            }
            
            $stmt->close();
        } else {
            $response['status'] = 'error';
            $response['message'] = 'ID required.';
        }
        break;

    default:
        $response['status'] = 'error';
        $response['message'] = 'Unsupported request method.';
        break;
}

$conn->close();
echo json_encode($response);
?>
