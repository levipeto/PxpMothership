<section class="w-full max-w-4xl mx-auto py-8 px-4">
    <flux:heading size="xl" class="mb-2">Legacy Database Connection Debug</flux:heading>
    <flux:text class="mb-8 text-zinc-600 dark:text-zinc-400">
        Test connectivity to the legacy RDS instance
    </flux:text>

    {{-- Configuration Display --}}
    <div class="mb-8 p-4 bg-zinc-50 dark:bg-zinc-800 rounded-lg">
        <flux:heading size="sm" class="mb-3">Current Configuration</flux:heading>
        <div class="grid grid-cols-2 gap-2 text-sm font-mono">
            @foreach ($this->configuration as $key => $value)
                <div class="text-zinc-500">{{ $key }}:</div>
                <div class="text-zinc-900 dark:text-zinc-100">{{ $value }}</div>
            @endforeach
        </div>
    </div>

    {{-- Timeout Setting --}}
    <div class="mb-6">
        <flux:input
            wire:model="customTimeout"
            type="number"
            min="1"
            max="60"
            label="Connection Timeout (seconds)"
            description="Default PDO timeout is 10 seconds. Try lower values to fail faster."
        />
    </div>

    {{-- Action Buttons --}}
    <div class="flex flex-wrap gap-3 mb-8">
        <flux:button wire:click="testConnection" variant="primary" wire:loading.attr="disabled">
            <span wire:loading.remove wire:target="testConnection">Test Raw PDO Connection</span>
            <span wire:loading wire:target="testConnection">Testing...</span>
        </flux:button>

        <flux:button wire:click="testWithLaravelConnection" wire:loading.attr="disabled">
            <span wire:loading.remove wire:target="testWithLaravelConnection">Test Laravel Connection</span>
            <span wire:loading wire:target="testWithLaravelConnection">Testing...</span>
        </flux:button>

        <flux:button wire:click="runNetworkDiagnostics" wire:loading.attr="disabled">
            <span wire:loading.remove wire:target="runNetworkDiagnostics">Run Network Diagnostics</span>
            <span wire:loading wire:target="runNetworkDiagnostics">Running...</span>
        </flux:button>
    </div>

    {{-- Results --}}
    @if ($connectionResult || $connectionError)
        <div class="mb-6">
            <flux:heading size="sm" class="mb-3">Connection Result</flux:heading>

            @if ($connectionTime !== null)
                <flux:badge class="mb-2" variant="{{ $connectionTime > 5000 ? 'warning' : 'default' }}">
                    Time: {{ $connectionTime }}ms
                </flux:badge>
            @endif

            @if ($connectionResult)
                <flux:callout variant="success" class="mb-3">
                    {{ $connectionResult }}
                </flux:callout>
            @endif

            @if ($connectionError)
                <flux:callout variant="danger" class="mb-3">
                    <div class="font-semibold">Connection Failed</div>
                    <div class="mt-1 text-sm font-mono break-all">{{ $connectionError }}</div>
                </flux:callout>
            @endif

            @if ($queryResult)
                <flux:callout variant="success">
                    {{ $queryResult }}
                </flux:callout>
            @endif
        </div>
    @endif

    {{-- Network Diagnostics --}}
    @if (!empty($networkDiagnostics))
        <div class="space-y-4">
            <flux:heading size="sm">Network Diagnostics</flux:heading>

            @if (isset($networkDiagnostics['dns']))
                <div class="p-4 bg-zinc-50 dark:bg-zinc-800 rounded-lg">
                    <div class="font-semibold mb-2">DNS Resolution</div>
                    <div class="grid grid-cols-2 gap-2 text-sm font-mono">
                        <div class="text-zinc-500">Host:</div>
                        <div>{{ $networkDiagnostics['dns']['host'] }}</div>
                        <div class="text-zinc-500">Resolved IP:</div>
                        <div>{{ $networkDiagnostics['dns']['resolved_ip'] }}</div>
                        <div class="text-zinc-500">DNS Resolved:</div>
                        <div>{{ $networkDiagnostics['dns']['is_ip'] ? 'Yes' : 'No (returned same as host)' }}</div>
                        <div class="text-zinc-500">Time:</div>
                        <div>{{ $networkDiagnostics['dns']['time_ms'] }}ms</div>
                    </div>
                </div>
            @endif

            @if (isset($networkDiagnostics['socket']))
                <div class="p-4 rounded-lg {{ $networkDiagnostics['socket']['status'] === 'success' ? 'bg-green-50 dark:bg-green-900/20' : 'bg-red-50 dark:bg-red-900/20' }}">
                    <div class="font-semibold mb-2">Socket Connection (Port {{ $networkDiagnostics['socket']['port'] }})</div>
                    <div class="grid grid-cols-2 gap-2 text-sm font-mono">
                        <div class="text-zinc-500">Status:</div>
                        <div class="{{ $networkDiagnostics['socket']['status'] === 'success' ? 'text-green-600' : 'text-red-600' }}">
                            {{ strtoupper($networkDiagnostics['socket']['status']) }}
                        </div>
                        <div class="text-zinc-500">Time:</div>
                        <div>{{ $networkDiagnostics['socket']['time_ms'] }}ms</div>
                        @if (isset($networkDiagnostics['socket']['error_message']))
                            <div class="text-zinc-500">Error:</div>
                            <div class="text-red-600">{{ $networkDiagnostics['socket']['error_message'] ?: 'Connection timed out' }}</div>
                        @endif
                    </div>
                </div>
            @endif

            @if (isset($networkDiagnostics['environment']))
                <div class="p-4 bg-zinc-50 dark:bg-zinc-800 rounded-lg">
                    <div class="font-semibold mb-2">Environment</div>
                    <div class="grid grid-cols-2 gap-2 text-sm font-mono">
                        <div class="text-zinc-500">App Environment:</div>
                        <div>{{ $networkDiagnostics['environment']['app_env'] }}</div>
                        <div class="text-zinc-500">Server IP:</div>
                        <div>{{ $networkDiagnostics['environment']['server_ip'] }}</div>
                        <div class="text-zinc-500">PHP Version:</div>
                        <div>{{ $networkDiagnostics['environment']['php_version'] }}</div>
                        <div class="text-zinc-500">PDO MySQL:</div>
                        <div>{{ $networkDiagnostics['environment']['pdo_mysql_available'] ? 'Available' : 'Not Available' }}</div>
                    </div>
                </div>
            @endif
        </div>
    @endif

    {{-- Troubleshooting Tips --}}
    <div class="mt-8 p-4 bg-amber-50 dark:bg-amber-900/20 rounded-lg">
        <flux:heading size="sm" class="mb-2">Troubleshooting Tips</flux:heading>
        <ul class="text-sm space-y-1 text-zinc-700 dark:text-zinc-300">
            <li><strong>10 second timeout:</strong> Usually indicates firewall/security group blocking the connection</li>
            <li><strong>Check AWS Security Groups:</strong> Ensure the Lambda/ECS security group can reach the RDS security group on port 3306</li>
            <li><strong>VPC Peering:</strong> If RDS is in a different VPC, verify peering and route tables</li>
            <li><strong>DNS resolution fails:</strong> Check VPC DNS settings and RDS endpoint</li>
            <li><strong>Socket connects but PDO fails:</strong> Check MySQL user permissions and host restrictions</li>
        </ul>
    </div>
</section>
