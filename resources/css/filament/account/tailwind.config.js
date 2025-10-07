import preset from '../../../../vendor/filament/filament/tailwind.config.preset'

export default {
    presets: [preset],
    content: [
        './app/Filament/Account/**/*.php',
        './resources/views/vendor/filament-accounts/**/*.blade.php',
        './vendor/filament/**/*.blade.php',
        './resources/views/filament/account/**/*.blade.php',
        './vendor/filament-accounts/**/*.blade.php'
    ],
}
