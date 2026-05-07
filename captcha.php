<?php
// Simple CAPTCHA image generator for COD confirmation
// Start output buffering and suppress PHP notices to avoid stray output
if (session_status() == PHP_SESSION_NONE) session_start();
if (!headers_sent()) ob_start();
@ini_set('display_errors', 0);

$length = 6;
$chars = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
$str = '';
for ($i = 0; $i < $length; $i++) {
    $str .= $chars[random_int(0, strlen($chars) - 1)];
}

// store lowercase for case-insensitive compare
$_SESSION['cod_captcha'] = strtolower($str);

// If GD is not available, output an SVG fallback image
if (!function_exists('imagecreatetruecolor') || !function_exists('imagepng')) {
    header('Content-Type: image/svg+xml');
    header('Cache-Control: no-cache, no-store, must-revalidate');
    header('Pragma: no-cache');
    $escaped = htmlspecialchars($str, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    // Build SVG without XML prolog to avoid parser issues if any stray bytes exist
    $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="150" height="50">\n';
    $svg .= '<rect width="100%" height="100%" fill="#f0f8ff" />\n';
    // noise lines
    for ($i = 0; $i < 6; $i++) {
        $x1 = random_int(0, 150); $y1 = random_int(0, 50); $x2 = random_int(0, 150); $y2 = random_int(0, 50);
        $svg .= "<line x1=\"$x1\" y1=\"$y1\" x2=\"$x2\" y2=\"$y2\" stroke=\"#6b7888\" stroke-width=\"1\" />\n";
    }
    $svg .= '<text x="50%" y="55%" font-family="Verdana,Arial" font-size="20" fill="#1e3c5a" text-anchor="middle">' . $escaped . '</text>\n';
    $svg .= '</svg>';
    // Remove UTF-8 BOM if present and flush
    $svg = preg_replace('/^\x{FEFF}/u', '', $svg);
    if (ob_get_length()) ob_clean();
    echo $svg;
    exit;
}

$width = 150;
$height = 50;
$img = imagecreatetruecolor($width, $height);

$bg = imagecolorallocate($img, 240, 248, 255);
$textColor = imagecolorallocate($img, 30, 60, 90);
$noiseColor = imagecolorallocate($img, 100, 120, 140);

imagefilledrectangle($img, 0, 0, $width, $height, $bg);

// add noise lines
for ($i = 0; $i < 6; $i++) {
    imageline($img, random_int(0, $width), random_int(0, $height), random_int(0, $width), random_int(0, $height), $noiseColor);
}

// add random dots
for ($i = 0; $i < 200; $i++) {
    imagesetpixel($img, random_int(0, $width), random_int(0, $height), $noiseColor);
}

// draw the characters with slight vertical jitter
$fontSize = 5; // built-in font size
$x = 12;
for ($i = 0; $i < strlen($str); $i++) {
    $char = $str[$i];
    // random vertical jitter
    $y = random_int(8, 22);
    // draw character
    imagestring($img, $fontSize, $x, $y, $char, $textColor);
    $x += 20 + random_int(-2, 4);
}

// send headers
if (ob_get_length()) ob_clean();
header('Content-Type: image/png');
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
imagepng($img);
imagedestroy($img);
exit;
?>
