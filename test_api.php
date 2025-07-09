<?php

class ApiTester
{
    private $baseUrl = 'http://127.0.0.1:8000/api/v1';
    private $token = null;

    public function makeRequest($endpoint, $method = 'GET', $data = null, $headers = [])
    {
        $url = $this->baseUrl . $endpoint;

        $defaultHeaders = [
            'Content-Type: application/json',
            'Accept: application/json'
        ];

        if ($this->token) {
            $defaultHeaders[] = 'Authorization: Bearer ' . $this->token;
        }

        $headers = array_merge($defaultHeaders, $headers);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);

        if ($data) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            return ['error' => $error, 'http_code' => 0];
        }

        return [
            'http_code' => $httpCode,
            'response' => json_decode($response, true),
            'raw_response' => $response
        ];
    }

    public function testLogin()
    {
        echo "=== Testing Login ===\n";
        $result = $this->makeRequest('/login', 'POST', [
            'email' => 'admin@example.com',
            'password' => 'password'
        ]);

        echo "HTTP Code: " . $result['http_code'] . "\n";

        if ($result['http_code'] === 200 && isset($result['response']['token'])) {
            $this->token = $result['response']['token'];
            echo "✅ Login successful! Token obtained.\n";
            echo "User: " . $result['response']['user']['name'] . " (" . $result['response']['user']['role'] . ")\n";
        } else {
            echo "❌ Login failed!\n";
            echo "Response: " . json_encode($result['response'], JSON_PRETTY_PRINT) . "\n";
        }
        echo "\n";
    }

    public function testGetUser()
    {
        echo "=== Testing Get Current User ===\n";
        $result = $this->makeRequest('/user');

        echo "HTTP Code: " . $result['http_code'] . "\n";

        if ($result['http_code'] === 200) {
            echo "✅ Get user successful!\n";
            echo "User: " . $result['response']['user']['name'] . "\n";
        } else {
            echo "❌ Get user failed!\n";
            echo "Response: " . json_encode($result['response'], JSON_PRETTY_PRINT) . "\n";
        }
        echo "\n";
    }

    public function testGetVehicles()
    {
        echo "=== Testing Get Vehicles ===\n";
        $result = $this->makeRequest('/vehicles');

        echo "HTTP Code: " . $result['http_code'] . "\n";

        if ($result['http_code'] === 200) {
            echo "✅ Get vehicles successful!\n";
            $vehicles = $result['response']['data'] ?? $result['response'];
            echo "Found " . count($vehicles) . " vehicles\n";
            if (count($vehicles) > 0) {
                echo "First vehicle: " . $vehicles[0]['registration_number'] . " - " . $vehicles[0]['model'] . "\n";
            }
        } else {
            echo "❌ Get vehicles failed!\n";
            echo "Response: " . json_encode($result['response'], JSON_PRETTY_PRINT) . "\n";
        }
        echo "\n";
    }

    public function testCreateVehicle()
    {
        echo "=== Testing Create Vehicle ===\n";
        $vehicleData = [
            'registration_number' => 'TEST-' . rand(100, 999),
            'model' => 'Test Vehicle Model',
            'year' => 2023
        ];

        $result = $this->makeRequest('/vehicles', 'POST', $vehicleData);

        echo "HTTP Code: " . $result['http_code'] . "\n";

        if ($result['http_code'] === 201) {
            echo "✅ Create vehicle successful!\n";
            echo "Created vehicle: " . $result['response']['vehicle']['registration_number'] . "\n";
            return $result['response']['vehicle']['id'];
        } else {
            echo "❌ Create vehicle failed!\n";
            echo "Response: " . json_encode($result['response'], JSON_PRETTY_PRINT) . "\n";
            return null;
        }
        echo "\n";
    }

    public function testGetUsers()
    {
        echo "=== Testing Get Users ===\n";
        $result = $this->makeRequest('/users');

        echo "HTTP Code: " . $result['http_code'] . "\n";

        if ($result['http_code'] === 200) {
            echo "✅ Get users successful!\n";
            $users = $result['response']['data'] ?? $result['response'];
            echo "Found " . count($users) . " users\n";
        } else {
            echo "❌ Get users failed!\n";
            echo "Response: " . json_encode($result['response'], JSON_PRETTY_PRINT) . "\n";
        }
        echo "\n";
    }

    public function testGetExchanges()
    {
        echo "=== Testing Get Exchanges ===\n";
        $result = $this->makeRequest('/exchanges');

        echo "HTTP Code: " . $result['http_code'] . "\n";

        if ($result['http_code'] === 200) {
            echo "✅ Get exchanges successful!\n";
            $exchanges = $result['response']['data'] ?? $result['response'];
            echo "Found " . count($exchanges) . " exchanges\n";
        } else {
            echo "❌ Get exchanges failed!\n";
            echo "Response: " . json_encode($result['response'], JSON_PRETTY_PRINT) . "\n";
        }
        echo "\n";
    }

    public function testVehicleDocuments($vehicleId)
    {
        if (!$vehicleId) return;

        echo "=== Testing Vehicle Documents ===\n";
        $result = $this->makeRequest("/vehicles/{$vehicleId}/documents");

        echo "HTTP Code: " . $result['http_code'] . "\n";

        if ($result['http_code'] === 200) {
            echo "✅ Get vehicle documents successful!\n";
            $documents = $result['response']['data'] ?? $result['response'];
            echo "Found " . count($documents) . " documents\n";
        } else {
            echo "❌ Get vehicle documents failed!\n";
            echo "Response: " . json_encode($result['response'], JSON_PRETTY_PRINT) . "\n";
        }
        echo "\n";
    }

    public function testVehicleMaintenances($vehicleId)
    {
        if (!$vehicleId) return;

        echo "=== Testing Vehicle Maintenances ===\n";
        $result = $this->makeRequest("/vehicles/{$vehicleId}/maintenances");

        echo "HTTP Code: " . $result['http_code'] . "\n";

        if ($result['http_code'] === 200) {
            echo "✅ Get vehicle maintenances successful!\n";
            $maintenances = $result['response']['data'] ?? $result['response'];
            echo "Found " . count($maintenances) . " maintenance records\n";
        } else {
            echo "❌ Get vehicle maintenances failed!\n";
            echo "Response: " . json_encode($result['response'], JSON_PRETTY_PRINT) . "\n";
        }
        echo "\n";
    }

    public function testErrorHandling()
    {
        echo "=== Testing Error Handling ===\n";

        // Test unauthorized access
        $tempToken = $this->token;
        $this->token = 'invalid-token';
        $result = $this->makeRequest('/vehicles');
        echo "Unauthorized access test: " . ($result['http_code'] === 401 ? "✅ PASS" : "❌ FAIL") . "\n";

        $this->token = $tempToken;

        // Test invalid vehicle creation
        $result = $this->makeRequest('/vehicles', 'POST', [
            'registration_number' => '', // Invalid empty
            'model' => '',
            'year' => 1800 // Invalid year
        ]);
        echo "Validation test: " . ($result['http_code'] === 422 ? "✅ PASS" : "❌ FAIL") . "\n";

        // Test non-existent vehicle
        $result = $this->makeRequest('/vehicles/99999');
        echo "Not found test: " . ($result['http_code'] === 404 ? "✅ PASS" : "❌ FAIL") . "\n";

        echo "\n";
    }

    public function testChauffeurAccess()
    {
        echo "=== Testing Chauffeur Access ===\n";

        // Login as chauffeur
        $result = $this->makeRequest('/login', 'POST', [
            'email' => 'chauffeur@example.com',
            'password' => 'password'
        ]);

        if ($result['http_code'] === 200) {
            $chauffeurToken = $result['response']['token'];
            $this->token = $chauffeurToken;

            // Test chauffeur can access vehicles
            $result = $this->makeRequest('/vehicles');
            echo "Chauffeur vehicle access: " . ($result['http_code'] === 200 ? "✅ PASS" : "❌ FAIL") . "\n";

            // Test chauffeur cannot access user management
            $result = $this->makeRequest('/users');
            echo "Chauffeur user access restriction: " . ($result['http_code'] === 403 ? "✅ PASS" : "❌ FAIL") . "\n";

            // Test my-vehicle endpoint
            $result = $this->makeRequest('/my-vehicle');
            echo "My vehicle endpoint: " . ($result['http_code'] === 200 ? "✅ PASS" : "❌ FAIL") . "\n";
        } else {
            echo "❌ Chauffeur login failed\n";
        }

        echo "\n";
    }

    public function runAllTests()
    {
        echo "🚀 Starting Comprehensive API Tests...\n\n";

        // Test authentication first
        $this->testLogin();

        if (!$this->token) {
            echo "❌ Cannot continue tests without authentication token!\n";
            return;
        }

        // Test user endpoints
        $this->testGetUser();

        // Test vehicle endpoints
        $this->testGetVehicles();
        $vehicleId = $this->testCreateVehicle();

        // Test user management
        $this->testGetUsers();

        // Test exchanges
        $this->testGetExchanges();

        // Test vehicle-specific endpoints
        $this->testVehicleDocuments($vehicleId);
        $this->testVehicleMaintenances($vehicleId);

        // Test error handling
        $this->testErrorHandling();

        // Test role-based access
        $this->testChauffeurAccess();

        echo "🏁 All comprehensive tests completed!\n";
    }
}

// Run the tests
$tester = new ApiTester();
$tester->runAllTests();
