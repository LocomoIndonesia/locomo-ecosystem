<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class KoaAtlasController extends Controller
{
    /**
     * Load the JSON manifest for the selected atlas
     */
    public function load($name)
    {
        $name = basename($name);
        $path = public_path("games/kartu-koa/assets/atlas/{$name}.json");

        if (!File::exists($path)) {
            return response()->json([
                'error' => 'Atlas not found',
                'path' => $path
            ], 404);
        }

        $content = File::get($path);
        return response()->json(json_decode($content, true));
    }

    /**
     * Save/overwrite the JSON manifest for the selected atlas
     */
    public function save(Request $request, $name)
    {
        $name = basename($name);
        $path = public_path("games/kartu-koa/assets/atlas/{$name}.json");

        $data = $request->all();

        if (empty($data)) {
            return response()->json([
                'success' => false,
                'message' => 'Empty request body'
            ], 400);
        }

        $formattedJson = json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        File::put($path, $formattedJson);

        return response()->json([
            'success' => true,
            'message' => "Atlas {$name}.json updated successfully."
        ]);
    }

    /**
     * Auto segment WebP image and generate JSON coordinates manifest file
     */
    public function autoSegment($name)
    {
        // Increase memory limit and execution time for large image connected-components BFS
        ini_set('memory_limit', '512M');
        set_time_limit(180);

        $name = basename($name);
        $webpPath = public_path("games/kartu-koa/assets/atlas/{$name}.webp");
        $jsonPath = public_path("games/kartu-koa/assets/atlas/{$name}.json");

        if (!File::exists($webpPath)) {
            return response()->json([
                'success' => false,
                'message' => 'WebP image file not found on server.'
            ], 404);
        }

        $img = @imagecreatefromwebp($webpPath);
        if (!$img) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to load WebP image using GD extension. Check if GD is enabled.'
            ], 500);
        }

        $width = imagesx($img);
        $height = imagesy($img);

        // Visited grid to track scanned pixels
        $visited = [];
        $frames = [];
        $itemCounter = 0;

        for ($y = 0; $y < $height; $y++) {
            for ($x = 0; $x < $width; $x++) {
                if (isset($visited[$y][$x])) {
                    continue;
                }

                $rgb = imagecolorat($img, $x, $y);
                $r = ($rgb >> 16) & 0xFF;
                $g = ($rgb >> 8) & 0xFF;
                $b = $rgb & 0xFF;
                $brightness = ($r + $g + $b) / 3;

                // Thresholding: components are bright (> 20)
                if ($brightness > 20) {
                    $queue = [[$y, $x]];
                    $visited[$y][$x] = true;
                    
                    $minY = $y; $maxY = $y;
                    $minX = $x; $maxX = $x;

                    while (!empty($queue)) {
                        list($cy, $cx) = array_shift($queue);
                        if ($cy < $minY) $minY = $cy;
                        if ($cy > $maxY) $maxY = $cy;
                        if ($cx < $minX) $minX = $cx;
                        if ($cx > $maxX) $maxX = $cx;

                        // Check 4 directional neighbors
                        foreach ([[-1, 0], [1, 0], [0, -1], [0, 1]] as $dir) {
                            $ny = $cy + $dir[0];
                            $nx = $cx + $dir[1];

                            if ($ny >= 0 && $ny < $height && $nx >= 0 && $nx < $width) {
                                if (!isset($visited[$ny][$nx])) {
                                    $nrgb = imagecolorat($img, $nx, $ny);
                                    $nr = ($nrgb >> 16) & 0xFF;
                                    $ng = ($nrgb >> 8) & 0xFF;
                                    $nb = $nrgb & 0xFF;
                                    $nbrightness = ($nr + $ng + $nb) / 3;

                                    if ($nbrightness > 20) {
                                        $visited[$ny][$nx] = true;
                                        $queue[] = [$ny, $nx];
                                    }
                                }
                            }
                        }
                    }

                    $w = $maxX - $minX + 1;
                    $h = $maxY - $minY + 1;

                    // Filter out noise elements
                    if ($w > 12 && $h > 12) {
                        $spriteName = "element_{$itemCounter}";
                        $frames[$spriteName] = [
                            "frame" => ["x" => $minX, "y" => $minY, "w" => $w, "h" => $h],
                            "sourceSize" => ["w" => $w, "h" => $h],
                            "spriteSourceSize" => ["x" => 0, "y" => 0, "w" => $w, "h" => $h]
                        ];
                        $itemCounter++;
                    }
                }
            }
        }

        imagedestroy($img);

        $manifest = [
            "frames" => $frames,
            "meta" => [
                "image" => "{$name}.webp",
                "format" => "RGBA8888",
                "size" => ["w" => $width, "h" => $height],
                "scale" => "1"
            ]
        ];

        $formattedJson = json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        File::put($jsonPath, $formattedJson);

        return response()->json([
            'success' => true,
            'message' => "Auto-segmentation completed. Generated {$name}.json with {$itemCounter} elements."
        ]);
    }
}
