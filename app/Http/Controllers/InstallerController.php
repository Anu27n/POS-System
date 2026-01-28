<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use App\Models\User;
use App\Models\SystemSetting;

class InstallerController extends Controller
{
    /**
     * License verification API endpoint
     */
    private const LICENSE_API_URL = 'https://license.styxcorp.in/verify.php';
    
    /**
     * Secret key for license verification
     */
    private const LICENSE_SECRET_KEY = 'styx_secret_2026';
    public function index()
    {
        // Check if already installed
        if ($this->isInstalled()) {
            return redirect('/');
        }

        // Ensure .env file exists (copy from .env.example if not)
        $this->ensureEnvExists();

        return view('installer.welcome');
    }

    public function requirements()
    {
        if ($this->isInstalled()) {
            return redirect('/');
        }

        // Ensure .env file exists
        $this->ensureEnvExists();
        $requirements = [
            'php' => [
                'required' => '8.1.0',
                'current' => PHP_VERSION,
                'passed' => version_compare(PHP_VERSION, '8.1.0', '>='),
            ],
            'extensions' => [
                'pdo' => extension_loaded('pdo'),
                'mbstring' => extension_loaded('mbstring'),
                'openssl' => extension_loaded('openssl'),
                'tokenizer' => extension_loaded('tokenizer'),
                'json' => extension_loaded('json'),
                'curl' => extension_loaded('curl'),
                'fileinfo' => extension_loaded('fileinfo'),
            ],
            'permissions' => [
                'storage/app' => is_writable(storage_path('app')),
                'storage/framework' => is_writable(storage_path('framework')),
                'storage/logs' => is_writable(storage_path('logs')),
                'bootstrap/cache' => is_writable(base_path('bootstrap/cache')),
            ],
        ];

        $allPassed = $requirements['php']['passed']
            && !in_array(false, $requirements['extensions'])
            && !in_array(false, $requirements['permissions']);

        return view('installer.requirements', compact('requirements', 'allPassed'));
    }

    public function license()
    {
        if ($this->isInstalled()) {
            return redirect('/');
        }

        // Check if license is already verified
        if ($this->isLicenseVerified()) {
            return redirect()->route('installer.database');
        }

        return view('installer.license');
    }

    public function licenseStore(Request $request)
    {
        if ($this->isInstalled()) {
            return redirect('/');
        }

        $request->validate([
            'purchase_code' => 'required|string|min:5',
        ]);

        try {
            // DEVELOPMENT BYPASS: Allow test purchase codes in non-production
            $testCodes = ['TEST-LICENSE-2026', 'DEV-LICENSE-KEY', 'STYX-DEV-2026'];
            if (config('app.env') !== 'production' && in_array(strtoupper($request->purchase_code), $testCodes)) {
                // Store the test purchase code
                $this->updateEnv([
                    'PURCHASE_CODE' => $request->purchase_code,
                ]);
                File::put(storage_path('license_key'), $request->purchase_code);
                
                return redirect()->route('installer.database');
            }

            // Call the license verification API
            $response = Http::timeout(30)->asForm()->post(self::LICENSE_API_URL, [
                'purchase_code' => $request->purchase_code,
                'domain' => $request->getHost(),
                'secret_key' => self::LICENSE_SECRET_KEY,
            ]);

            if (!$response->successful()) {
                return back()->withErrors([
                    'purchase_code' => 'Unable to connect to license server. Please check your internet connection and try again.'
                ])->withInput();
            }

            $result = $response->json();

            if (!isset($result['valid']) || !$result['valid']) {
                $errorMessage = $result['message'] ?? 'Invalid purchase code. Please check your purchase code and try again.';
                return back()->withErrors([
                    'purchase_code' => $errorMessage
                ])->withInput();
            }

            // License is valid - store the purchase code in .env
            $this->updateEnv([
                'PURCHASE_CODE' => $request->purchase_code,
            ]);

            // Also store in a file as backup (in case .env is not readable)
            File::put(storage_path('license_key'), $request->purchase_code);

            return redirect()->route('installer.database');
        } catch (\Exception $e) {
            return back()->withErrors([
                'purchase_code' => 'License verification failed: ' . $e->getMessage()
            ])->withInput();
        }
    }

    /**
     * Check if license has been verified
     */
    private function isLicenseVerified(): bool
    {
        // Check if purchase code exists in .env
        $envPurchaseCode = env('PURCHASE_CODE');
        if (!empty($envPurchaseCode)) {
            return true;
        }

        // Check if license file exists as backup
        if (File::exists(storage_path('license_key'))) {
            $licenseKey = trim(File::get(storage_path('license_key')));
            return !empty($licenseKey);
        }

        return false;
    }

    public function database()
    {
        if ($this->isInstalled()) {
            return redirect('/');
        }

        // Ensure license is verified before allowing database configuration
        if (!$this->isLicenseVerified()) {
            return redirect()->route('installer.license')
                ->withErrors(['purchase_code' => 'Please verify your purchase code first.']);
        }

        return view('installer.database');
    }

    public function databaseStore(Request $request)
    {
        if ($this->isInstalled()) {
            return redirect('/');
        }

        $request->validate([
            'db_connection' => 'required|in:mysql,sqlite,pgsql',
            'db_host' => 'required_unless:db_connection,sqlite',
            'db_port' => 'required_unless:db_connection,sqlite',
            'db_database' => 'required_unless:db_connection,sqlite',
            'db_username' => 'required_unless:db_connection,sqlite',
            'db_password' => 'nullable',
        ]);

        try {
            // Test database connection
            if ($request->db_connection === 'sqlite') {
                $dbPath = database_path('database.sqlite');
                if (!File::exists($dbPath)) {
                    File::put($dbPath, '');
                }
            } else {
                config([
                    'database.default' => $request->db_connection,
                    'database.connections.' . $request->db_connection => [
                        'driver' => $request->db_connection,
                        'host' => $request->db_host,
                        'port' => $request->db_port,
                        'database' => $request->db_database,
                        'username' => $request->db_username,
                        'password' => $request->db_password ?? '',
                        'charset' => 'utf8mb4',
                        'collation' => 'utf8mb4_unicode_ci',
                        'prefix' => '',
                    ],
                ]);

                DB::purge($request->db_connection);
                DB::connection($request->db_connection)->getPdo();
            }

            // Update .env file
            $this->updateEnv([
                'DB_CONNECTION' => $request->db_connection,
                'DB_HOST' => $request->db_host ?? '127.0.0.1',
                'DB_PORT' => $request->db_port ?? '3306',
                'DB_DATABASE' => $request->db_connection === 'sqlite'
                    ? database_path('database.sqlite')
                    : $request->db_database,
                'DB_USERNAME' => $request->db_username ?? '',
                'DB_PASSWORD' => $request->db_password ?? '',
            ]);

            return redirect()->route('installer.migrations');
        } catch (\Exception $e) {
            return back()->withErrors(['database' => 'Database connection failed: ' . $e->getMessage()]);
        }
    }

    public function migrations()
    {
        if ($this->isInstalled()) {
            return redirect('/');
        }

        return view('installer.migrations');
    }

    public function migrationsRun()
    {
        if ($this->isInstalled()) {
            return redirect('/');
        }

        try {
            Artisan::call('migrate', ['--force' => true]);

            // Create storage symlink for public file access (QR codes, images, etc.)
            if (!file_exists(public_path('storage'))) {
                Artisan::call('storage:link');
            }

            return redirect()->route('installer.admin');
        } catch (\Exception $e) {
            return back()->withErrors(['migration' => 'Migration failed: ' . $e->getMessage()]);
        }
    }

    public function admin()
    {
        if ($this->isInstalled()) {
            return redirect('/');
        }

        return view('installer.admin');
    }

    public function adminStore(Request $request)
    {
        if ($this->isInstalled()) {
            return redirect('/');
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'password' => 'required|string|min:8|confirmed',
            'app_name' => 'required|string|max:255',
        ]);

        try {
            // Create admin user
            $admin = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => 'admin',
                'email_verified_at' => now(),
            ]);

            // Update app name in .env
            $this->updateEnv([
                'APP_NAME' => '"' . $request->app_name . '"',
            ]);

            // Create initial system settings
            SystemSetting::updateOrCreate(
                ['key' => 'app_name'],
                ['value' => $request->app_name]
            );

            SystemSetting::updateOrCreate(
                ['key' => 'installed_at'],
                ['value' => now()->toIso8601String()]
            );

            // Store the purchase code in database for persistence
            $purchaseCode = env('PURCHASE_CODE');
            if (empty($purchaseCode) && File::exists(storage_path('license_key'))) {
                $purchaseCode = trim(File::get(storage_path('license_key')));
            }
            if (!empty($purchaseCode)) {
                SystemSetting::updateOrCreate(
                    ['key' => 'purchase_code'],
                    ['value' => $purchaseCode]
                );
            }

            // Create installed file
            File::put(storage_path('installed'), now()->toIso8601String());

            return redirect()->route('installer.complete');
        } catch (\Exception $e) {
            return back()->withErrors(['admin' => 'Failed to create admin: ' . $e->getMessage()]);
        }
    }

    public function complete()
    {
        return view('installer.complete');
    }

    private function isInstalled(): bool
    {
        return File::exists(storage_path('installed'));
    }

    /**
     * Ensure .env file exists. Create from .env.example if not.
     * Also generates APP_KEY if not set.
     */
    private function ensureEnvExists(): void
    {
        $envPath = base_path('.env');
        $examplePath = base_path('.env.example');

        // If .env doesn't exist, create from .env.example
        if (!File::exists($envPath)) {
            if (File::exists($examplePath)) {
                File::copy($examplePath, $envPath);
            } else {
                // Create a minimal .env file
                $minimalEnv = "APP_NAME=\"POS System\"\n";
                $minimalEnv .= "APP_ENV=production\n";
                $minimalEnv .= "APP_KEY=\n";
                $minimalEnv .= "APP_DEBUG=false\n";
                $minimalEnv .= "APP_TIMEZONE=Asia/Kolkata\n";
                $minimalEnv .= "APP_URL=http://localhost\n\n";
                $minimalEnv .= "DB_CONNECTION=mysql\n";
                $minimalEnv .= "DB_HOST=127.0.0.1\n";
                $minimalEnv .= "DB_PORT=3306\n";
                $minimalEnv .= "DB_DATABASE=pos_system\n";
                $minimalEnv .= "DB_USERNAME=root\n";
                $minimalEnv .= "DB_PASSWORD=\n\n";
                $minimalEnv .= "SESSION_DRIVER=file\n";
                $minimalEnv .= "CACHE_STORE=file\n";
                $minimalEnv .= "QUEUE_CONNECTION=sync\n";
                
                File::put($envPath, $minimalEnv);
            }
        }

        // Generate APP_KEY if not set
        $envContent = File::get($envPath);
        if (preg_match('/^APP_KEY=$/m', $envContent) || preg_match('/^APP_KEY=\s*$/m', $envContent)) {
            // Generate a new key
            $key = 'base64:' . base64_encode(random_bytes(32));
            $this->updateEnv(['APP_KEY' => $key]);
        }
    }

    private function updateEnv(array $values): void
    {
        $envPath = base_path('.env');
        
        // Ensure .env exists before trying to update it
        if (!File::exists($envPath)) {
            $this->ensureEnvExists();
        }
        
        $envContent = File::get($envPath);

        foreach ($values as $key => $value) {
            $pattern = "/^{$key}=.*/m";
            $replacement = "{$key}={$value}";

            if (preg_match($pattern, $envContent)) {
                $envContent = preg_replace($pattern, $replacement, $envContent);
            } else {
                $envContent .= "\n{$replacement}";
            }
        }

        File::put($envPath, $envContent);
    }
}
