const preset = require('../../../vendor/filament/filament/tailwind.config.preset')

module.exports = {
    presets: [preset],
    content: [
        './app/Filament/**/*.php',
        './resources/views/**/*.blade.php',
        './vendor/filament/**/*.blade.php',
    ],
    safelist: [
        'stage-button',
        'state-container',
        'state',
        'border-primary-500',
        'break-inside-avoid',
        'pt-3',
    ],
}
