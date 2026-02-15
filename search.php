<?php
header('Content-Type: application/json');

// 1. Load the data
$json_data = file_get_contents('sm_data.json');
$data = json_decode($json_data, true);

// 2. Handle search by ID
if (isset($_GET['query'])) {
    $query = strtoupper($_GET['query']);
    $results = array_filter($data, function($item) use ($query) {
        return (strpos(strtoupper($item['FAT_ID']), $query) !== false) || 
               (strpos(strtoupper($item['OLT_ID']), $query) !== false);
    });
    echo json_encode(array_values($results));
    exit;
}

// 3. Handle search by Coordinate (Nearest Neighbors)
if (isset($_GET['lat']) && isset($_GET['long'])) {
    $user_lat = (float)$_GET['lat'];
    $user_long = (float)$_GET['long'];
    $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;

    // Haversine formula to calculate distance in km
    function calculateDistance($lat1, $lon1, $lat2, $lon2) {
        $earth_radius = 6371; // Radius in kilometers
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat/2) * sin($dLat/2) +
             cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
             sin($dLon/2) * sin($dLon/2);
        $c = 2 * atan2(sqrt($a), sqrt(1-$a));
        return $earth_radius * $c;
    }

    // Calculate distance for each point
    foreach ($data as &$item) {
        $item['distance_km'] = calculateDistance($user_lat, $user_long, $item['lat'], $item['long']);
    }

    // Sort by distance
    usort($data, function($a, $b) {
        return $a['distance_km'] <=> $b['distance_km'];
    });

    // Return the closest results
    echo json_encode(array_slice($data, 0, $limit));
    exit;
}

// Default response if no parameters provided
echo json_encode(["error" => "Please provide lat/long or a search query."]);
?>
