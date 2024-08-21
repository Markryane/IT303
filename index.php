<?php
header('Content-Type: application/json');
$response = array("method" => $_SERVER['REQUEST_METHOD']);

// Retrieve raw JSON data from request body
$data = file_get_contents('php://input');

// // Decode JSON data
$decodedData = json_decode($data, true);

// // Access individual fields
$name = $decodedData['name'] ?? '';
$age = $decodedData['age'];
$course = $decodedData['course'];
$year = $decodedData['year'];

// // Add the fields to the response array
$response['data']['name'] = $name;
$response['data']['age'] = $age;
$response['data']['course'] = $course;
$response['data']['year'] = $year;

echo json_encode($response);
?>