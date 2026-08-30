<style>
    :root {
        --color-primary: {{ $brand->primaryColor() }};
        --color-primary-dark: color-mix(in srgb, {{ $brand->primaryColor() }} 80%, black);
        --color-primary-light: color-mix(in srgb, {{ $brand->primaryColor() }} 70%, white);
        --color-accent: {{ $brand->accentColor() }};
        --color-accent-dark: color-mix(in srgb, {{ $brand->accentColor() }} 80%, black);
    }
</style>
