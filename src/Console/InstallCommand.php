<?php

namespace Rotaz\FilamentAccounts\Console;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;

use function Laravel\Prompts\confirm;
use function Laravel\Prompts\error;
use function Laravel\Prompts\info;

class InstallCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'filament-accounts:install {--force : Overwrite existing files}';

    /**
     * The console command description.
     *
     * @var string|null
     */
    protected $description = 'Install the Filament Accounts package';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        if ($this->checkExistingInstallation() === static::FAILURE) {
            return static::FAILURE;
        }

        info('Installing Filament Accounts...');

        try {
            $this->commonInstallation();
            $this->installFilamentAccounts();

        } catch (\Exception $e) {
            Log::error('Installation error while installing Filament Accounts: ' . $e->getMessage());

            error('An error occurred while installing Filament Accounts. Please check the log for more information.');

            return static::FAILURE;
        }

        return static::SUCCESS;
    }

    protected function checkExistingInstallation(): int
    {
        $force = $this->option('force');

        if ($force === false && File::exists(app_path('Providers/FilamentAccountsServiceProvider.php'))) {
            $shouldProceed = confirm(
                label: 'Filament Accounts is already installed. Would you like to proceed with the installation?',
                default: false,
                yes: 'Yes, proceed with the installation',
                no: 'No, abort the installation',
                hint: 'By continuing, some files may be overwritten. If necessary, it is recommended to backup your application before proceeding.',
            );

            if ($shouldProceed === false) {
                info('Filament Accounts installation aborted.');

                return static::FAILURE;
            }
        }

        return static::SUCCESS;
    }

    protected function commonInstallation(): void
    {
        // Storage...
        $this->callSilent('storage:link');

        // Update Welcome Page...
        $this->updateWelcomePage();

        $this->updateVite();

        // Configure Session...
        $this->configureSession();

        // Publish...
        $this->callSilent('vendor:publish', [
            '--tag' => 'filament-accounts-migrations',
            '--force' => true,
        ]);

        $this->callSilent('vendor:publish', [
            '--tag' => 'filament-accounts-seeder',
            '--force' => true,
        ]);

        $this->callSilent('vendor:publish', [
            '--tag' => 'filament-accounts-public',
            '--force' => true,
        ]);

        // Sanctum...
        $this->call('install:api', [
            '--without-migration-prompt' => true,
        ]);

        // Directories...
        (new Filesystem)->ensureDirectoryExists(app_path('Actions/FilamentAccounts'));
        (new Filesystem)->ensureDirectoryExists(app_path('Policies'));
        (new Filesystem)->ensureDirectoryExists(resource_path('markdown'));

        // Delete Directories...
        (new Filesystem)->deleteDirectory(resource_path('sass'));

        // Terms Of Service / Privacy Policy...
        $this->copyStubFiles('resources/markdown', resource_path('markdown'), ['terms.md', 'policy.md']);

        // Factories...
        copy(__DIR__ . '/../../database/factories/UserFactory.php', base_path('database/factories/UserFactory.php'));
        copy(__DIR__ . '/../../database/factories/AccountFactory.php', base_path('database/factories/AccountFactory.php'));

        // Actions...
        $this->copyStubFiles('app/Actions/FilamentAccounts', app_path('Actions/FilamentAccounts'), [
            'AddAccountParty.php',
            'CreateAccount.php',
            'CreateNewUser.php',
            'DeleteAccount.php',
            'InviteAccountParty.php',
            'RemoveAccountParty.php',
            'UpdateAccountName.php',
            'UpdateUserPassword.php',
            'UpdateUserProfileInformation.php',
        ]);

        // Policies...
        $this->copyStubFiles('app/Policies', app_path('Policies'), ['AccountPolicy.php']);

        // Models...
        $this->copyStubFiles('app/Models', app_path('Models'), ['Account.php', 'AccountInvitation.php', 'Party.php']);
    }

    /**
     * Update the default welcome page.
     */
    protected function updateWelcomePage(): void
    {
        $filePath = resource_path('views/welcome.blade.php');

        if (file_exists($filePath)) {
            $fileContents = file_get_contents($filePath);

            $alreadyExists = Str::contains($fileContents, 'filament()->getHomeUrl()');

            if ($alreadyExists) {
                return;
            }

            $this->replaceInFile("Route::has('login')", 'filament()->getLoginUrl()', $filePath);
            $this->replaceInFile("Route::has('register')", 'filament()->getRegistrationUrl()', $filePath);
            $this->replaceInFile('Dashboard', '{{ ucfirst(filament()->getCurrentPanel()->getId()) }}', $filePath);
            $this->replaceInFile("{{ url('/dashboard') }}", '{{ filament()->getHomeUrl() }}', $filePath);
            $this->replaceInFile("{{ route('login') }}", '{{ filament()->getLoginUrl() }}', $filePath);
            $this->replaceInFile("{{ route('register') }}", '{{ filament()->getRegistrationUrl() }}', $filePath);
        }
    }

    protected function updateVite(): void
    {
        $filePath = base_path('vite.config.js');

        if (file_exists($filePath)) {

            $fileContents = file_get_contents($filePath);

            $alreadyExists = Str::contains($fileContents, 'resources/css/filament/account/theme.css');

            if ($alreadyExists) {
                return;
            }

            $viteExists = Str::contains($fileContents, "'resources/js/app.js'],");

            if ($viteExists) {
                $this->replaceInFile(
                    "'resources/js/app.js'],",
                    "'resources/js/app.js','resources/css/filament/account/theme.css'],",
                    $filePath
                );
            }

        }
    }

    /**
     * Configure the session driver for Account.
     */
    protected function configureSession(): void
    {
        $this->replaceInFile('SESSION_DRIVER=cookie', 'SESSION_DRIVER=database', base_path('.env'));
        $this->replaceInFile('SESSION_DRIVER=cookie', 'SESSION_DRIVER=database', base_path('.env.example'));
    }

    /**
     * Install the FilamentAccounts account stack into the application.
     */
    protected function installFilamentAccounts(): void
    {

        info('Filament Accounts support installed successfully.');

        // Service Providers...
        $this->copyStubFiles('app/Providers', app_path('Providers'), [
            'FilamentAccountsServiceProvider.php',
        ]);

        ServiceProvider::addProviderToBootstrapFile('App\Providers\FilamentAccountsServiceProvider');

        // Models...
        $this->copyStubFiles('app/Models', app_path('Models'), ['User.php']);

        $this->copyStubFiles('app/Models', app_path('Models'), ['ConnectedAccount.php']);

        copy(__DIR__ . '/../../stubs/app/Providers/Filament/UserPanelProvider.php', app_path('Providers/Filament/UserPanelProvider.php'));

        // Actions...
        copy(__DIR__ . '/../../stubs/app/Actions/FilamentAccounts/DeleteUserWithSocialite.php', app_path('Actions/FilamentAccounts/DeleteUser.php'));

        $this->copyStubFiles('app/Actions/FilamentAccounts', app_path('Actions/FilamentAccounts'), [
            'CreateConnectedAccount.php',
            'CreateUserFromProvider.php',
            'HandleInvalidState.php',
            'ResolveSocialiteUser.php',
            'SetUserPassword.php',
            'UpdateConnectedAccount.php',
        ]);

        // Policies...
        $this->copyStubFiles('app/Policies', app_path('Policies'), ['ConnectedAccountPolicy.php']);

    }

    protected function copyStubFiles(string $sourceSubPath, string $destinationPath, array $files): void
    {
        foreach ($files as $file) {
            copy(__DIR__ . '/../../stubs/' . $sourceSubPath . '/' . $file, $destinationPath . '/' . $file);
        }
    }

    /**
     * Replace a given string within a given file.
     */
    protected function replaceInFile(string $search, string $replace, string $path): void
    {
        file_put_contents($path, str_replace($search, $replace, file_get_contents($path)));
    }
}
