<?php

namespace App\Models\Tenant;

use Hyn\Tenancy\Abstracts\TenantModel;
use Illuminate\Database\Eloquent\Model;

class CustomColorTheme extends TenantModel
{
    protected $fillable = [
        'name',
        'primary',
        'secondary',
        'tertiary',
        'quaternary',
        'is_light',
        'is_default',
        'user_id',
    ];

    protected $casts = [
        'is_light' => 'boolean',
        'is_default' => 'boolean',
    ];

    /**
     * Relación con el usuario
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Genera el SVG del tema para la vista previa
     */
    public function generateSvg()
    {
        // URL-encode los colores para el data URI
        $primary = urlencode($this->primary);
        $secondary = urlencode($this->secondary);
        $tertiary = urlencode($this->tertiary);
        $quaternary = urlencode($this->quaternary);

        return "data:image/svg+xml,%3Csvg xmlns=%27http://www.w3.org/2000/svg%27 viewBox=%270 0 125 28%27%3E%3Cdefs%3E%3Cstyle%3E.cls-1%7Bfill:{$primary};%7D.cls-2%7Bfill:{$secondary};%7D.cls-3%7Bfill:{$tertiary};%7D.cls-4%7Bfill:{$quaternary};%7D%3C/style%3E%3C/defs%3E%3Crect class=%27cls-1%27 width=%2749%27 height=%2728%27 rx=%275%27/%3E%3Crect class=%27cls-2%27 x=%2757%27 width=%2730%27 height=%2728%27 rx=%275%27/%3E%3Crect class=%27cls-3%27 x=%2795%27 width=%2730%27 height=%2711%27 rx=%275%27/%3E%3Crect class=%27cls-4%27 x=%2795%27 y=%2717%27 width=%2730%27 height=%2711%27 rx=%275%27/%3E%3C/svg%3E";
    }

    /**
     * Genera las variables CSS para el tema
     */
    public function generateCss()
    {
        $primary = $this->hexToRgb($this->primary);
        $secondary = $this->hexToRgb($this->secondary);
        $tertiary = $this->hexToRgb($this->tertiary);
        $quaternary = $this->hexToRgb($this->quaternary);

        $primaryDarker = $this->darkenColor($this->primary, 15);
        $secondaryDarker = $this->darkenColor($this->secondary, 15);
        $tertiaryDarker = $this->darkenColor($this->tertiary, 15);
        $quaternaryDarker = $this->darkenColor($this->quaternary, 30);

        // Calcular gradientes más claros
        $gradient1 = $this->lightenColor($this->primary, 5);
        $gradient2 = $this->lightenColor($this->primary, 10);
        $gradient3 = $this->lightenColor($this->primary, 15);
        $gradient1Darker = $this->darkenColor($gradient1, 15);
        $gradient2Darker = $this->darkenColor($gradient2, 15);
        $gradient3Darker = $this->darkenColor($gradient3, 15);

        // Variables según tipo de tema (claro/oscuro)
        if ($this->is_light) {
            $body = '#4e4e4e';
            $bodyRgb = '78, 78, 78';
            $alternate = '#7c7c7c';
            $alternateRgb = '124, 124, 124';
            $muted = '#afafaf';
            $mutedRgb = '175, 175, 175';
            $separator = '#dddddd';
            $separatorRgb = '221, 221, 221';
            $separatorLight = '#f1f1f1';
            $separatorLightRgb = '241, 241, 241';
            $background = '#f9f9f9';
            $backgroundRgb = '249, 249, 249';
            $foreground = '#ffffff';
            $foregroundRgb = '255, 255, 255';
            $backgroundTheme = '#eaf0f1';
            $backgroundLight = '#f8f8f8';
            $lightText = '#ffffff';
            $lightTextRgb = '255, 255, 255';
            $darkText = '#343a40';
            $darkTextRgb = '52, 58, 64';
            $lightTextDarker = '#eeeeee';
            $darkTextDarker = '#23272b';
            $bodyDarker = '#333333';
            $alternateDarker = '#616161';
            $mutedDarker = '#888888';
            $separatorDarker = '#c0c0c0';
            $menuShadow = '0px 3px 10px rgba(0, 0, 0, 0.12)';
            $menuShadowNavcolor = '0px 3px 10px rgba(0, 0, 0, 0.07)';
            $backgroundNavcolorLight = '#fff';
            $backgroundNavcolorDark = '#253a52';
            $themeImageFilter = 'hue-rotate(0deg)';
        } else {
            $body = '#c1c1c1';
            $bodyRgb = '193, 193, 193';
            $alternate = '#999999';
            $alternateRgb = '153, 153, 153';
            $muted = '#727272';
            $mutedRgb = '114, 114, 114';
            $separator = '#474747';
            $separatorRgb = '71, 71, 71';
            $separatorLight = '#2e2e2e';
            $separatorLightRgb = '46, 46, 46';
            $background = '#1d1d1d';
            $backgroundRgb = '29, 29, 29';
            $foreground = '#242424';
            $foregroundRgb = '36, 36, 36';
            $backgroundTheme = '#242424';
            $backgroundLight = '#292929';
            $lightText = '#f0f0f0';
            $lightTextRgb = '240, 240, 240';
            $darkText = '#191c1f';
            $darkTextRgb = '25, 28, 31';
            $lightTextDarker = '#e9e9e9';
            $darkTextDarker = '#08090a';
            $bodyDarker = '#a0a0a0';
            $alternateDarker = '#6e6e6e';
            $mutedDarker = '#4e4e4e';
            $separatorDarker = '#353535';
            $menuShadow = '0px 3px 10px rgba(0, 0, 0, 0.2)';
            $menuShadowNavcolor = '0px 3px 10px rgba(0, 0, 0, 0.4)';
            $backgroundNavcolorLight = '#fff';
            $backgroundNavcolorDark = '#242424';
            $themeImageFilter = 'hue-rotate(0deg) brightness(0.8)';
        }

        // Colores de estado (danger, success, info, warning) - SIEMPRE los mismos independientemente del tema
        $danger = '#cf2637';
        $dangerRgb = '207, 38, 55';
        $dangerDarker = '#771a23';

        $info = '#279aac';
        $infoRgb = '39, 154, 172';
        $infoDarker = '#19545d';

        $warning = '#ebb71a';
        $warningRgb = '235, 183, 26';
        $warningDarker = '#aa830f';

        $success = '#439b38';
        $successRgb = '67, 155, 56';
        $successDarker = '#285422';

        $light = '#dadada';
        $lightRgb = '218, 218, 218';
        $lightDarker = '#c9c9c9';

        $dark = '#4e4e4e';
        $darkRgb = '78, 78, 78';
        $darkDarker = '#282828';

        return "
html[data-color='custom-{$this->id}'] {
  --primary: {$this->primary};
  --secondary: {$this->secondary};
  --tertiary: {$this->tertiary};
  --quaternary: {$this->quaternary};
  --primary-rgb: {$primary};
  --secondary-rgb: {$secondary};
  --tertiary-rgb: {$tertiary};
  --quaternary-rgb: {$quaternary};
  --primary-darker: {$primaryDarker};
  --secondary-darker: {$secondaryDarker};
  --tertiary-darker: {$tertiaryDarker};
  --quaternary-darker: {$quaternaryDarker};
  --body: {$body};
  --alternate: {$alternate};
  --muted: {$muted};
  --separator: {$separator};
  --separator-light: {$separatorLight};
  --body-rgb: {$bodyRgb};
  --alternate-rgb: {$alternateRgb};
  --muted-rgb: {$mutedRgb};
  --separator-rgb: {$separatorRgb};
  --separator-light-rgb: {$separatorLightRgb};
  --background: {$background};
  --foreground: {$foreground};
  --background-rgb: {$backgroundRgb};
  --foreground-rgb: {$foregroundRgb};
  --background-theme: {$backgroundTheme};
  --background-light: {$backgroundLight};
  --gradient-1: {$gradient1};
  --gradient-2: {$gradient2};
  --gradient-3: {$gradient3};
  --gradient-1-darker: {$gradient1Darker};
  --gradient-2-darker: {$gradient2Darker};
  --gradient-3-darker: {$gradient3Darker};
  --light-text: {$lightText};
  --dark-text: {$darkText};
  --light-text-darker: {$lightTextDarker};
  --dark-text-darker: {$darkTextDarker};
  --light-text-rgb: {$lightTextRgb};
  --dark-text-rgb: {$darkTextRgb};
  --danger: {$danger};
  --info: {$info};
  --warning: {$warning};
  --success: {$success};
  --light: {$light};
  --dark: {$dark};
  --danger-darker: {$dangerDarker};
  --info-darker: {$infoDarker};
  --warning-darker: {$warningDarker};
  --success-darker: {$successDarker};
  --light-darker: {$lightDarker};
  --dark-darker: {$darkDarker};
  --body-darker: {$bodyDarker};
  --alternate-darker: {$alternateDarker};
  --muted-darker: {$mutedDarker};
  --separator-darker: {$separatorDarker};
  --danger-rgb: {$dangerRgb};
  --info-rgb: {$infoRgb};
  --warning-rgb: {$warningRgb};
  --success-rgb: {$successRgb};
  --light-rgb: {$lightRgb};
  --dark-rgb: {$darkRgb};
  --menu-shadow: {$menuShadow};
  --menu-shadow-navcolor: {$menuShadowNavcolor};
  --background-navcolor-light: {$backgroundNavcolorLight};
  --background-navcolor-dark: {$backgroundNavcolorDark};
  --theme-image-filter: {$themeImageFilter};
}
";
    }

    /**
     * Convierte HEX a RGB
     */
    private function hexToRgb($hex)
    {
        $hex = str_replace('#', '', $hex);
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));
        return "{$r}, {$g}, {$b}";
    }

    /**
     * Oscurece un color HEX
     */
    private function darkenColor($hex, $percent)
    {
        $hex = str_replace('#', '', $hex);
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));

        $r = max(0, $r - ($r * $percent / 100));
        $g = max(0, $g - ($g * $percent / 100));
        $b = max(0, $b - ($b * $percent / 100));

        return sprintf("#%02x%02x%02x", $r, $g, $b);
    }

    /**
     * Aclara un color HEX
     */
    private function lightenColor($hex, $percent)
    {
        $hex = str_replace('#', '', $hex);
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));

        $r = min(255, $r + ((255 - $r) * $percent / 100));
        $g = min(255, $g + ((255 - $g) * $percent / 100));
        $b = min(255, $b + ((255 - $b) * $percent / 100));

        return sprintf("#%02x%02x%02x", $r, $g, $b);
    }
}
