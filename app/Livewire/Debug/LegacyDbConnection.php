<?php

namespace App\Livewire\Debug;

use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Layout;
use Livewire\Component;
use PDO;

#[Layout('components.layouts.app')]
class LegacyDbConnection extends Component
{
    public bool $testing = false;

    public ?string $connectionResult = null;

    public ?string $connectionError = null;

    public ?float $connectionTime = null;

    public ?string $queryResult = null;

    public ?string $queryError = null;

    public int $customTimeout = 5;

    /** @var array<string, mixed> */
    public array $networkDiagnostics = [];

    public function testConnection(): void
    {
        $this->reset(['connectionResult', 'connectionError', 'connectionTime', 'queryResult', 'queryError']);
        $this->testing = true;

        $startTime = microtime(true);

        try {
            $config = config('database.connections.legacy');
            $host = $config['host'];
            $port = $config['port'];
            $database = $config['database'];
            $username = $config['username'];
            $password = $config['password'];

            $dsn = "mysql:host={$host};port={$port};dbname={$database}";

            $pdo = new PDO($dsn, $username, $password, [
                PDO::ATTR_TIMEOUT => $this->customTimeout,
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            ]);

            $this->connectionTime = round((microtime(true) - $startTime) * 1000, 2);
            $this->connectionResult = 'Connected successfully';

            // Try a simple query
            $stmt = $pdo->query('SELECT 1 as test');
            $result = $stmt->fetch(PDO::FETCH_ASSOC);
            $this->queryResult = 'Query successful: '.json_encode($result);

        } catch (\PDOException $e) {
            $this->connectionTime = round((microtime(true) - $startTime) * 1000, 2);
            $this->connectionError = $e->getMessage();
        } finally {
            $this->testing = false;
        }
    }

    public function testWithLaravelConnection(): void
    {
        $this->reset(['connectionResult', 'connectionError', 'connectionTime', 'queryResult', 'queryError']);
        $this->testing = true;

        $startTime = microtime(true);

        try {
            DB::connection('legacy')->reconnect();
            $result = DB::connection('legacy')->select('SELECT 1 as test');

            $this->connectionTime = round((microtime(true) - $startTime) * 1000, 2);
            $this->connectionResult = 'Laravel connection successful';
            $this->queryResult = 'Query result: '.json_encode($result);

        } catch (\Exception $e) {
            $this->connectionTime = round((microtime(true) - $startTime) * 1000, 2);
            $this->connectionError = $e->getMessage();
        } finally {
            $this->testing = false;
        }
    }

    public function runNetworkDiagnostics(): void
    {
        $config = config('database.connections.legacy');
        $host = $config['host'];
        $port = $config['port'];

        $this->networkDiagnostics = [];

        // DNS resolution
        $startTime = microtime(true);
        $ip = gethostbyname($host);
        $dnsTime = round((microtime(true) - $startTime) * 1000, 2);

        $this->networkDiagnostics['dns'] = [
            'host' => $host,
            'resolved_ip' => $ip,
            'is_ip' => $ip !== $host,
            'time_ms' => $dnsTime,
        ];

        // Socket connection test
        $startTime = microtime(true);
        $socket = @fsockopen($host, (int) $port, $errno, $errstr, $this->customTimeout);
        $socketTime = round((microtime(true) - $startTime) * 1000, 2);

        if ($socket) {
            fclose($socket);
            $this->networkDiagnostics['socket'] = [
                'status' => 'success',
                'port' => $port,
                'time_ms' => $socketTime,
            ];
        } else {
            $this->networkDiagnostics['socket'] = [
                'status' => 'failed',
                'port' => $port,
                'error_code' => $errno,
                'error_message' => $errstr,
                'time_ms' => $socketTime,
            ];
        }

        // Environment info
        $this->networkDiagnostics['environment'] = [
            'app_env' => config('app.env'),
            'server_ip' => $_SERVER['SERVER_ADDR'] ?? gethostbyname(gethostname()),
            'php_version' => PHP_VERSION,
            'pdo_mysql_available' => extension_loaded('pdo_mysql'),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public function getConfigurationProperty(): array
    {
        $config = config('database.connections.legacy');

        return [
            'driver' => $config['driver'] ?? 'not set',
            'host' => $config['host'] ?? 'not set',
            'port' => $config['port'] ?? 'not set',
            'database' => $config['database'] ?? 'not set',
            'username' => $config['username'] ?? 'not set',
            'password' => isset($config['password']) ? str_repeat('*', min(strlen($config['password']), 8)) : 'not set',
            'charset' => $config['charset'] ?? 'not set',
            'collation' => $config['collation'] ?? 'not set',
        ];
    }

    public function render()
    {
        return view('livewire.debug.legacy-db-connection');
    }
}
