<?php

$method = $_SERVER['REQUEST_METHOD'];

if($method == "GET") {
	parse();
}

function parse() {
	$body = "Robert Quinlan, 4068, Shelter";
	$conn = new PDO("mysql:host=127.0.0.1;dbname=HelpText", "root", "Shylah6525");
	preg_match("/\d{4}/", $body, $matches);
	$postcode = $matches[0];

	/*$from = $_POST['from'];
	$to = $_POST['to'];*/

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

	for($i = 0; $i < count($services); $i++) {
		$body = $services[$i]['name'] . "\r\n" . $services[$i]['phone'] . "\r\n" . $services[$i]['address'];
		echo $body;
	}

	/*sendSMS($to, $from, $body);*/
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

	if($serviceType) {
		$sql .= "WHERE ";
		for($i = 0; $i < count($searchTypes); $i++) {
			$sql .= "Service_Type  = '" . $searchTypes[$i] . "'";
			if($i+1 != count($searchTypes)) {
				$sql .= " OR ";
			}
		}
	}

	$sql .= "ORDER BY (POW((Longitude-:lon),2) + POW((Latitude-:lat),2)) LIMIT 3";


	$result  = $conn->prepare($sql);
	$result->execute(['lat' => $coords[0], 'lon' => $coords[1]]);

	$services = [];
	while($row = $result->fetch()) {
		array_push($services, ['name' => $row['Location_Name'], 'phone' => $row['Service_Phone'], 'address' => $row['Location_Address_1'] . ", " . ucwords(strtolower($row['Location_Suburb']))]);
	}
	return $services;
}

function sendSMS($from, $to, $body) {
	$ch = curl_init();

	curl_setopt($ch, CURLOPT_URL, "https://api-mapper.clicksend.com/http/v2/send.php");
	curl_setopt($ch, CURLOPT_POST, 1);
	curl_setopt($ch, CURLOPT_POSTFIELDS, "method=http&username=Quinlan96&key=34ACB9B5-3DD0-DED5-7DB9-6AA79218F907&to=" . $from . "&senderid=" . $to . "&message=" . $body);

	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

	$server_output = curl_exec($ch);

	curl_close($ch);
}

?>
