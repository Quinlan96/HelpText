<?php

$method = $_SERVER['REQUEST_METHOD'];
$body = "Robert Quinlan, 4068, VAKS";
$number = "61111111111";

if($method == "GET") {
	parse($body);
}

function parse($body) {
	$conn = new PDO("mysql:host=127.0.0.1;dbname=HelpText", "root", "Shylah6525");
	preg_match("/\d{4}/", $body, $matches);
	$postcode = $matches[0];

	preg_match("/(food|shelter|medical)/i", $body, $matches);
	$serviceType = strtolower($matches[0]);

	$searchTypes = [];

	switch($serviceType) {
		case 'food':
			array_push($searchTypes, "Food Vans and Mobile Kitchens", "Meals on Wheels");
			break;
		case 'shelter':
			array_push($searchTypes, "Crisis & Emergency Accommodation", "Youth Accommodation Services");
			break;
		case 'medical':
			array_push($searchTypes, "Abuse & Assault Services", "General Crisis and Emergency Services", "General Health Services", "General Practice/Doctor", "Health Screening Services", "Mental Health Services", "General Welfare & Support Services", "Hospitals", "Suicide & Self Harm Information");
	}

	$coords = getCoords($conn, $postcode);

	$services = findServices($conn, $coords, $serviceType, $searchTypes);

	$messageBody = "Hi John, here are some accommodation options near you:";
}

function getCoords($conn, $postcode) {
	$sql = "SELECT lat, lon FROM postcode_db WHERE postcode = :postcode LIMIT 1";

	$result = $conn->prepare($sql);
	$result->execute(['postcode' => $postcode]);

	if($row = $result->fetch()) {
		$lat = $row['lat'];	
		$lon = $row['lon'];
	}
	return [$lat, $lon];
}

function findServices($conn, $coords, $serviceType, $searchTypes) {
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
	$result->execute(['lat' => $coords[0], 'lon' => $coords[1]]);

	while($row = $result->fetch()) {
		echo "<p>" . $row['Location_Name'] . ", " . $row['Location_Postcode'] . "</p>";
	}
}

?>
