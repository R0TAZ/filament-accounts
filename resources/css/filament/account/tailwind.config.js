import preset from '../../../../vendor/filament/filament/tailwind.config.preset'

export default {
    presets: [preset],
    content: [
        './app/Filament/Account/**/*.php',
        './resources/views/filament/account/**/*.blade.php',
        './vendor/filament/**/*.blade.php',
        './vendor/rotaz/filament-accounts/resources/views/**/*.blade.php'
    ],
}
