<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\File;
use App\Models\User;
use App\Models\SystemSetting;

class InstallerController extends Controller
{
    public function index()
    {
        // Check if already installed
        if ($this->isInstalled()) {
            return redirect('/');
        }

        return view('installer.welcome');
    }

    public function requirements()
    {
        if ($this->isInstalled()) {
            return redirect('/');
        }

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

    public function database()
    {
        if ($this->isInstalled()) {
            return redirect('/');
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

    private function updateEnv(array $values): void
    {
        $envPath = base_path('.env');
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
