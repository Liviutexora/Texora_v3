<?php

namespace App\Http\Controllers\Installer;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use Exception;

class InstallerController extends Controller
{
    protected function applyDatabaseConfig(array $dbConfig): void
    {
        // .env changes are not picked up automatically during the same request.
        // Re-apply the installer-provided credentials to the runtime config.
        config([
            'database.default' => 'mysql',
            'database.connections.mysql.host' => $dbConfig['DB_HOST'] ?? config('database.connections.mysql.host'),
            'database.connections.mysql.port' => $dbConfig['DB_PORT'] ?? config('database.connections.mysql.port'),
            'database.connections.mysql.database' => $dbConfig['DB_DATABASE'] ?? config('database.connections.mysql.database'),
            'database.connections.mysql.username' => $dbConfig['DB_USERNAME'] ?? config('database.connections.mysql.username'),
            'database.connections.mysql.password' => $dbConfig['DB_PASSWORD'] ?? config('database.connections.mysql.password'),
        ]);

        DB::purge('mysql');
        DB::reconnect('mysql');

        // Force a connection attempt so we fail early with a clear error.
        DB::connection()->getPdo();
    }

    public function index()
    {
        return view('installer.welcome');
    }

    public function requirements()
    {
        $requirements = $this->checkRequirements();
        return view('installer.requirements', compact('requirements'));
    }

    public function permissions()
    {
        $permissions = $this->checkPermissions();
        return view('installer.permissions', compact('permissions'));
    }

    public function database()
    {
        return view('installer.database');
    }

    public function saveDatabase(Request $request)
    {
        $request->validate([
            'database_hostname' => 'required',
            'database_port' => 'required|numeric',
            'database_name' => 'required',
            'database_username' => 'required',
        ]);

        try {
            config([
                'database.connections.mysql.host' => $request->database_hostname,
                'database.connections.mysql.port' => $request->database_port,
                'database.connections.mysql.database' => $request->database_name,
                'database.connections.mysql.username' => $request->database_username,
                'database.connections.mysql.password' => $request->database_password ?? '',
            ]);

            DB::purge('mysql');
            DB::reconnect('mysql');
            DB::connection()->getPdo();

            // Get full base URL including subdirectory path
            $appUrl = $this->getBaseUrl();

            // Store in session instead of writing .env now to avoid triggering
            // dev server restart mid-install (which can cause port conflict).
            // .env is written once at the end in finish().
            session([
                'installer.db_config' => [
                    'DB_HOST' => $request->database_hostname,
                    'DB_PORT' => $request->database_port,
                    'DB_DATABASE' => $request->database_name,
                    'DB_USERNAME' => $request->database_username,
                    'DB_PASSWORD' => $request->database_password ?? '',
                ],
                'installer.app_url' => $appUrl,
            ]);

            return redirect()->route('installer.admin');

        } catch (Exception $e) {
            Log::error('Database connection failed during installation', [
                'exception' => $e->getMessage(),
                'host' => $request->database_hostname,
            ]);
            return back()->withErrors(['database' => __('Unable to connect to the database with the provided information.')]);
        }
    }

    public function admin()
    {
        $this->restoreDatabaseConfigFromSession();
        return view('installer.admin');
    }

    public function saveAdmin(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'password' => 'required|min:8|confirmed',
            'site_title' => 'required|string|max:255',
            'site_description' => 'nullable|string|max:10000',
        ]);

        try {
            $this->restoreDatabaseConfigFromSession();

            Artisan::call('migrate', ['--force' => true]);
            Artisan::call('db:seed', ['--force' => true]);

            $start_with_demo_data = $request->start_with_demo_data ?? 0;
            if ($start_with_demo_data == 1) {
                Artisan::call('db:seed', [
                    '--class' => 'Database\Seeders\BookingSaasSeeder',
                    '--force' => true,
                ]);
            }

            // Use raw DB queries throughout so that DEMO_MODE=true cannot silently
            // block saves via the AppServiceProvider eloquent.saving wildcard listener.
            DB::transaction(function () use ($request) {
                $now = now()->toDateTimeString();

                $existing = DB::table('users')->where('email', $request->email)->first();
                if ($existing) {
                    DB::table('users')->where('email', $request->email)->update([
                        'name'              => $request->name,
                        'email_verified_at' => $now,
                        'password'          => Hash::make($request->password),
                        'is_active'         => 1,
                        'updated_at'        => $now,
                    ]);
                    $userId = $existing->id;
                } else {
                    $userId = DB::table('users')->insertGetId([
                        'name'              => $request->name,
                        'email'             => $request->email,
                        'email_verified_at' => $now,
                        'password'          => Hash::make($request->password),
                        'is_active'         => 1,
                        'created_at'        => $now,
                        'updated_at'        => $now,
                    ]);
                }

                // Assign super_admin role via raw DB (same pattern as BookingSaasSeeder)
                $role = DB::table('roles')
                    ->where('name', 'super_admin')
                    ->where('guard_name', 'web')
                    ->first();
                if ($role) {
                    DB::table('model_has_roles')->insertOrIgnore([
                        'role_id'    => $role->id,
                        'model_type' => 'App\\Models\\User',
                        'model_id'   => $userId,
                    ]);
                }

                // Update settings via raw DB
                foreach ([
                    'site_name'        => $request->site_title,
                    'site_description' => $request->site_description ?? 'Your awesome description',
                ] as $key => $value) {
                    $exists = DB::table('settings')->where('key', $key)->exists();
                    if ($exists) {
                        DB::table('settings')->where('key', $key)->update([
                            'value'      => $value,
                            'updated_at' => $now,
                        ]);
                    } else {
                        DB::table('settings')->insert([
                            'key'        => $key,
                            'value'      => $value,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ]);
                    }
                }
            });

            return redirect()->route('installer.finish');
        } catch (\Illuminate\Database\QueryException $e) {
            Log::error('Database error during installation', ['exception' => $e]);
            return back()->withErrors(['admin' => __('Database error occurred during installation. Please check your database configuration.')]);
        } catch (Exception $e) {
            Log::error('Installation failed', ['exception' => $e]);
            return back()->withErrors(['admin' => __('Installation failed: ') . $e->getMessage()]);
        }
    }

    public function finish()
    {
        // Write .env once at the end to avoid triggering dev server restart mid-install
        $dbConfig = session('installer.db_config');
        $appUrl = session('installer.app_url');
        if ($dbConfig && $appUrl) {
            $this->updateEnv(array_merge(
                [
                    'APP_URL'       => $appUrl,
                    'ASSET_URL'     => $appUrl,
                    'DB_CONNECTION' => 'mysql',
                    'APP_ENV'       => 'production',
                    'APP_DEBUG'     => 'false',
                    'LOG_LEVEL'     => 'error',
                    'DEMO_MODE'     => 'false',
                ],
                $dbConfig
            ));

            // Re-apply immediately so db:seed (same request) uses the correct credentials.
            $this->applyDatabaseConfig($dbConfig);

            session()->forget(['installer.db_config', 'installer.app_url']);
        } elseif ($dbConfig) {
            // In case appUrl isn't set for some reason, still apply DB config for seeders.
            $this->applyDatabaseConfig($dbConfig);
        }

        // Create installed file
        File::put(base_path('.installed'), date('Y-m-d H:i:s'));
        
        // Generate app key if not exists
        if (empty(config('app.key'))) {
            Artisan::call('key:generate');
        }
        
        // Livewire assets: prefer dedicated command, fallback to vendor publish tag.
        // We log explicit status so production support can diagnose install issues quickly.
        if (class_exists(\Livewire\Livewire::class) || class_exists(\Livewire\LivewireServiceProvider::class)) {
            try {
                Artisan::call('livewire:publish', ['--assets' => true]);
            } catch (\Symfony\Component\Console\Exception\CommandNotFoundException $e) {
                try {
                    Artisan::call('vendor:publish', [
                        '--tag' => 'livewire:assets',
                        '--force' => true,
                    ]);
                } catch (\Throwable $fallbackException) {
                    Log::warning('Livewire detected but asset publish failed.', [
                        'primary_error' => $e->getMessage(),
                        'fallback_error' => $fallbackException->getMessage(),
                    ]);
                }
            } catch (\Throwable $e) {
                Log::warning('Livewire asset publish returned an error.', [
                    'error' => $e->getMessage(),
                ]);
            }
        } else {
            Log::warning('Livewire package not detected during installation; skipping asset publish.');
        }
        
        syncModulePermission();
        return view('installer.finish');
    }

    protected function checkRequirements(): array
    {
        return [
            'php_version' => [
                'name' => 'PHP Version >= 8.2',
                'check' => version_compare(PHP_VERSION, '8.2.0', '>='),
            ],
            'bcmath' => [
                'name' => 'BCMath Extension',
                'check' => extension_loaded('bcmath'),
            ],
            'ctype' => [
                'name' => 'Ctype Extension',
                'check' => extension_loaded('ctype'),
            ],
            'json' => [
                'name' => 'JSON Extension',
                'check' => extension_loaded('json'),
            ],
            'mbstring' => [
                'name' => 'Mbstring Extension',
                'check' => extension_loaded('mbstring'),
            ],
            'openssl' => [
                'name' => 'OpenSSL Extension',
                'check' => extension_loaded('openssl'),
            ],
            'pdo' => [
                'name' => 'PDO Extension',
                'check' => extension_loaded('pdo'),
            ],
            'pdo_mysql' => [
                'name' => 'PDO MySQL Extension',
                'check' => extension_loaded('pdo_mysql'),
            ],
            'tokenizer' => [
                'name' => 'Tokenizer Extension',
                'check' => extension_loaded('tokenizer'),
            ],
            'xml' => [
                'name' => 'XML Extension',
                'check' => extension_loaded('xml'),
            ],
            'zip' => [
                'name' => 'ZIP Extension',
                'check' => extension_loaded('zip'),
            ],
        ];
    }

    protected function checkPermissions(): array
    {
        return [
            'storage' => [
                'name' => 'storage',
                'path' => storage_path(),
                'check' => is_writable(storage_path()),
            ],
            'bootstrap_cache' => [
                'name' => 'bootstrap/cache',
                'path' => base_path('bootstrap/cache'),
                'check' => is_writable(base_path('bootstrap/cache')),
            ],
            'public' => [
                'name' => 'public',
                'path' => base_path('public'),
                'check' => is_writable(base_path('public')),
            ], 
        ];
    }

    /**
     * Restore database config from session (used when .env is deferred until finish).
     */
    protected function restoreDatabaseConfigFromSession(): void
    {
        $dbConfig = session('installer.db_config');
        if (!$dbConfig) {
            return;
        }
        config([
            'database.connections.mysql.host' => $dbConfig['DB_HOST'] ?? config('database.connections.mysql.host'),
            'database.connections.mysql.port' => $dbConfig['DB_PORT'] ?? config('database.connections.mysql.port'),
            'database.connections.mysql.database' => $dbConfig['DB_DATABASE'] ?? config('database.connections.mysql.database'),
            'database.connections.mysql.username' => $dbConfig['DB_USERNAME'] ?? config('database.connections.mysql.username'),
            'database.connections.mysql.password' => $dbConfig['DB_PASSWORD'] ?? config('database.connections.mysql.password'),
        ]);
        DB::purge('mysql');
        DB::reconnect('mysql');
    }

    protected function getBaseUrl(): string
    {
        // Auto-detect subdirectory path and return full base URL
        if (isset($_SERVER['SCRIPT_NAME'])) {
            $scheme = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
            $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
            
            // Get the script name to determine the subdirectory
            // When accessed via /LaraPressLanding/modular-starter/, the SCRIPT_NAME will be 
            // /LaraPressLanding/modular-starter/public/index.php
            $scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
            
            // Extract subdirectory from script name
            // Remove /public/index.php to get the subdirectory
            // $subdirectory = preg_replace('#/public/index\.php$#', '', $scriptName);
            $subdirectory = "/";
            
            // Clean up subdirectory
            if ($subdirectory === '/' || $subdirectory === '\\' || empty($subdirectory)) {
                $subdirectory = '';
            }
            
            $rootUrl = $scheme . '://' . $host . $subdirectory;
            return rtrim($rootUrl, '/');
        }
        
        // Fallback to request root if SCRIPT_NAME is not available
        return request()->root();
    }

    protected function updateEnv(array $data): void
    {
        $envPath = base_path('.env');
        $envContent = File::get($envPath);

        foreach ($data as $key => $value) {
            // Check if key exists in .env
            if (preg_match("/^{$key}=.*/m", $envContent)) {
                // Replace existing value
                // preg_replace treats `$` in the replacement string specially, so escape it.
                $safeValue = str_replace('$', '\$', (string) $value);
                $envContent = preg_replace(
                    "/^{$key}=.*/m",
                    "{$key}={$safeValue}",
                    $envContent
                );
            } else {
                // Add new key at the end
                $envContent .= "\n{$key}={$value}";
            }
        }

        File::put($envPath, $envContent);

        // Fresh installs can create .env without APP_KEY; generate one immediately.
        $hasAppKey = (bool) preg_match('/^APP_KEY=base64:[A-Za-z0-9+\/=]+$/m', $envContent);
        if (! $hasAppKey) {
            Artisan::call('key:generate', ['--force' => true]);
        }
    }
}