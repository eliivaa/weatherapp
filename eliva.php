<?php
// Establish database connection
$serverName = "localhost";
$userName = "root";
$password = "";
$conn = mysqli_connect($serverName, $userName, $password);
if (!$conn) {
    // Connection failed
    exit;
}

// Create database if not exists
$createDatabase = "CREATE DATABASE IF NOT EXISTS weatherdata";
if (!mysqli_query($conn, $createDatabase)) {
    // Error creating database
    exit;
}

// Select the weatherdata database
mysqli_select_db($conn, 'weatherdata');

// Create weather table if not exists
$createTable = "CREATE TABLE IF NOT EXISTS weatherdata(
    id INT AUTO_INCREMENT PRIMARY KEY,
    temperature VARCHAR(255) NOT NULL,
    present_city VARCHAR(255) NOT NULL,
    condition_detail VARCHAR(255) NOT NULL,
    humidity VARCHAR(255) NOT NULL,
    wind VARCHAR(255) NOT NULL,
    pressure VARCHAR(255) NOT NULL,
    date_time VARCHAR(255) NOT NULL,
    max VARCHAR(255) NOT NULL,
    icon VARCHAR(255) NOT NULL,
    last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
)";
if (!mysqli_query($conn, $createTable)) {
    // Error creating table
    exit;
}

$cityName = isset($_GET['t']) ? $_GET['t'] : "Dumfries";
$apiKey = "1536febfa54e587e5f1b046441a6810b";
$url = "https://api.openweathermap.org/data/2.5/weather?units=metric&q=$cityName&appid=$apiKey";
$response = file_get_contents($url);
$data = json_decode($response, true);

if ($data && $data['cod'] == 200) {
    $temperature = round($data['main']['temp']);
    $present_city = $data['name'];
    $condition_detail = $data['weather'][0]['description'];
    $humidity = $data['main']['humidity'] . "%";
    $wind = $data['wind']['speed'] . "m/s";
    $pressure = $data['main']['pressure'] . "hPa";
    $date_time = date('D, M j, Y');
    $max = round($data['main']['temp_max']);
    $icon = $data['weather'][0]['icon'];

    $existingData = "SELECT * FROM weatherdata WHERE present_city = '$present_city' AND date_time = '$date_time'";
    $result = mysqli_query($conn, $existingData);

    if (mysqli_num_rows($result) == 0) {
        $insertData = "INSERT INTO weatherdata (temperature, present_city, condition_detail, humidity, wind, pressure, date_time, max, icon)
        VALUES ('$temperature', '$present_city', '$condition_detail', '$humidity', '$wind', '$pressure', '$date_time', '$max', '$icon')";

        if (!mysqli_query($conn, $insertData)) {
            // Failed to insert data
        }
    } else {
        $row = mysqli_fetch_assoc($result);
        $lastUpdated = strtotime($row['last_updated']);
        $currentTime = time();
        $timeDiff = $currentTime - $lastUpdated;

        if ($timeDiff > 7200) { // 7200 seconds = 2 hours
            $updateData = "UPDATE weatherdata SET temperature = '$temperature', condition_detail = '$condition_detail', humidity = '$humidity', wind = '$wind', pressure = '$pressure', max = '$max', icon = '$icon'
            WHERE present_city = '$present_city' AND date_time = '$date_time'";

            if (!mysqli_query($conn, $updateData)) {
                // Failed to update data
            }
        }
    }

    // Fetching data from weather table based on city name again after insertion
    $selectAllData = "SELECT * FROM weatherdata WHERE present_city = '$present_city'";
    $result = mysqli_query($conn, $selectAllData);
    $rows = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $rows[] = $row;
    }
    // Encoding fetched data to JSON and sending as response
    $json_data = json_encode($rows);
    header('Content-Type: application/json');
    echo $json_data;
} else {
    echo "Error fetching weather data";
}

mysqli_close($conn);
?>
