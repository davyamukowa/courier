<?php

defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Installable PWA shell for riders — reachable at the clean top-level URL
 * /rider (see modules/courier_goshipping/config/routes.php). Fully public;
 * the app itself handles login/registration client-side against Rider_api.
 */
class Rider_app extends App_Controller
{
    public function index()
    {
        $this->load->view('rider_app/shell');
    }

    public function manifest()
    {
        header('Content-Type: application/manifest+json');
        echo json_encode([
            'name'             => 'Go Shipping Rider',
            'short_name'       => 'GS Rider',
            'start_url'        => site_url('courier_goshipping/rider'),
            'scope'            => site_url('courier_goshipping/rider'),
            'display'          => 'standalone',
            'orientation'      => 'portrait',
            'background_color' => '#0d1b2a',
            'theme_color'      => '#0d47a1',
            'icons'            => [
                ['src' => site_url('courier_goshipping/rider/icon/192'), 'sizes' => '192x192', 'type' => 'image/png', 'purpose' => 'any maskable'],
                ['src' => site_url('courier_goshipping/rider/icon/512'), 'sizes' => '512x512', 'type' => 'image/png', 'purpose' => 'any maskable'],
            ],
        ]);
    }

    public function sw()
    {
        header('Content-Type: application/javascript');
        header('Service-Worker-Allowed: /');

        $cache_name = 'gs-rider-v1';
        echo <<<JS
const CACHE_NAME = '{$cache_name}';

self.addEventListener('install', function (event) {
    self.skipWaiting();
});

self.addEventListener('activate', function (event) {
    event.waitUntil(
        caches.keys().then(function (keys) {
            return Promise.all(keys.filter(function (k) { return k !== CACHE_NAME; }).map(function (k) { return caches.delete(k); }));
        }).then(function () { return self.clients.claim(); })
    );
});

// App-shell caching only — every API call goes straight to the network so
// a rider never acts on stale delivery/pickup data.
self.addEventListener('fetch', function (event) {
    if (event.request.method !== 'GET' || event.request.url.indexOf('/rider-api/') !== -1) {
        return;
    }
    event.respondWith(
        caches.match(event.request).then(function (cached) {
            var fetchPromise = fetch(event.request).then(function (response) {
                if (response && response.status === 200) {
                    var clone = response.clone();
                    caches.open(CACHE_NAME).then(function (cache) { cache.put(event.request, clone); });
                }
                return response;
            }).catch(function () { return cached; });
            return cached || fetchPromise;
        })
    );
});
JS;
    }

    // PWA home-screen icon — uses the CRM's own configured company logo
    // (Setup > Settings > Company, same file the waybill header/invoices
    // use) composited onto a square canvas, since an uploaded logo is
    // rarely already square and a distorted/cropped icon looks broken on
    // a home screen. Falls back to a generated placeholder if no logo is
    // configured or it can't be read.
    public function icon($size = 192)
    {
        $size = (int) $size;
        if (!in_array($size, [192, 512], true)) {
            $size = 192;
        }

        if (!function_exists('imagecreatetruecolor')) {
            header('Content-Type: image/gif');
            echo base64_decode('R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBTAA7');
            return;
        }

        header('Content-Type: image/png');

        $img = $this->build_logo_icon($size) ?: $this->build_placeholder_icon($size);

        imagepng($img);
        imagedestroy($img);
    }

    private function build_logo_icon($size)
    {
        $logo_file = get_option('company_logo_dark') ?: get_option('company_logo');
        if (empty($logo_file)) {
            return null;
        }

        $logo_path = FCPATH . 'uploads/company/' . $logo_file;
        if (!is_file($logo_path)) {
            return null;
        }

        $info = @getimagesize($logo_path);
        if (!$info) {
            return null;
        }

        switch ($info[2]) {
            case IMAGETYPE_PNG:
                $source = @imagecreatefrompng($logo_path);
                break;
            case IMAGETYPE_JPEG:
                $source = @imagecreatefromjpeg($logo_path);
                break;
            case IMAGETYPE_GIF:
                $source = @imagecreatefromgif($logo_path);
                break;
            case IMAGETYPE_WEBP:
                $source = function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($logo_path) : false;
                break;
            default:
                $source = false;
        }
        if (!$source) {
            return null;
        }

        $canvas = imagecreatetruecolor($size, $size);
        imagesavealpha($canvas, true);
        $transparent = imagecolorallocatealpha($canvas, 0, 0, 0, 127);
        imagefill($canvas, 0, 0, $transparent);

        // White backdrop — most company logos are dark-on-transparent or
        // assume a light background, and a maskable icon with a fully
        // transparent edge gets clipped oddly on some Android launchers.
        $white = imagecolorallocate($canvas, 255, 255, 255);
        imagefilledellipse($canvas, (int) ($size / 2), (int) ($size / 2), $size, $size, $white);

        // Fit the logo inside ~78% of the circle, centered, preserving
        // aspect ratio so it isn't stretched.
        $src_w = imagesx($source);
        $src_h = imagesy($source);
        $target_box = (int) ($size * 0.78);
        $scale = min($target_box / $src_w, $target_box / $src_h);
        $dst_w = (int) round($src_w * $scale);
        $dst_h = (int) round($src_h * $scale);
        $dst_x = (int) (($size - $dst_w) / 2);
        $dst_y = (int) (($size - $dst_h) / 2);

        imagealphablending($canvas, true);
        imagecopyresampled($canvas, $source, $dst_x, $dst_y, 0, 0, $dst_w, $dst_h, $src_w, $src_h);
        imagedestroy($source);

        return $canvas;
    }

    private function build_placeholder_icon($size)
    {
        $img = imagecreatetruecolor($size, $size);
        imagesavealpha($img, true);
        $transparent = imagecolorallocatealpha($img, 0, 0, 0, 127);
        imagefill($img, 0, 0, $transparent);

        $blue = imagecolorallocate($img, 13, 71, 161);
        imagefilledellipse($img, (int) ($size / 2), (int) ($size / 2), $size, $size, $blue);

        $red = imagecolorallocate($img, 198, 40, 40);
        $bar_h = (int) ($size * 0.16);
        imagefilledrectangle($img, 0, (int) ($size * 0.66), $size, (int) ($size * 0.66) + $bar_h, $red);

        $white = imagecolorallocate($img, 255, 255, 255);
        $font_size = (int) ($size * 0.42);
        if (function_exists('imagettftext') && file_exists(APPPATH . 'third_party/mpdf/ttfonts/DejaVuSans-Bold.ttf')) {
            $bbox = imagettfbbox($font_size, 0, APPPATH . 'third_party/mpdf/ttfonts/DejaVuSans-Bold.ttf', 'G');
            $text_w = abs($bbox[4] - $bbox[0]);
            $text_h = abs($bbox[5] - $bbox[1]);
            imagettftext($img, $font_size, 0, (int) (($size - $text_w) / 2), (int) (($size + $text_h) / 2) - (int) ($size * 0.08), $white, APPPATH . 'third_party/mpdf/ttfonts/DejaVuSans-Bold.ttf', 'G');
        } else {
            imagefilledellipse($img, (int) ($size / 2), (int) ($size / 2) - (int) ($size * 0.05), (int) ($size * 0.34), (int) ($size * 0.34), $white);
            imagefilledellipse($img, (int) ($size / 2), (int) ($size / 2) - (int) ($size * 0.05), (int) ($size * 0.20), (int) ($size * 0.20), $blue);
        }

        return $img;
    }
}
