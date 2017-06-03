<?php

$conn = new PDO("mysql:host=127.0.0.1;dbname=HelpText", "root", "Shylah6525");

$method = $_SERVER['REQUEST_METHOD'];
$postcode = 4068;
$serviceType = "food";
$searchTypes = [];

switch($serviceType) {
	case 'food':
		array_push($searchTypes, "Food Vans and Mobile Kitchens", "Meals on Wheels");
		break;
	case 'shelter':
		array_push($searchTypes, "Crisis & Emergency Accommodation", "Youth Accommodation Services");
		break;
	case 'medical':
}

$sql = "SELECT lat, lon FROM postcode_db WHERE postcode = :postcode LIMIT 1";

$result = $conn->prepare($sql);
$result->execute(['postcode' => $postcode]);

if($row = $result->fetch()) {
	$lat = $row['lat'];	
	$lon = $row['lon'];
}

$sql = "SELECT * FROM services ";

echo $search;

if($serviceType) {
	$sql .= "WHERE ";
	for($i = 0; $i < count($searchTypes); $i++) {
		$sql .= "Service_Type  = '" . $searchTypes[$i] . "'";
		if($i+1 != count($searchTypes)) {
			$sql .= " OR ";
		}
	}
}

$sql .= "ORDER BY (POW((Longitude-:lon),2) + POW((Latitude-:lat),2)) LIMIT 5";


$result  = $conn->prepare($sql);
$result->execute(['lat' => $lat, 'lon' => $lon]);

while($row = $result->fetch()) {
	echo "<p>" . $row['Location_Name'] . ", " . $row['Location_Postcode'] . "</p>";
}

?>
