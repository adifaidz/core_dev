<?php
/**
 * $Id$
 *
 * Image handling helper functions
 *
 * @author Martin Lindhe, 2007-2008 <martin@startwars.org>
 */

$config['image']['resample_resized']    = true;        ///< use imagecopyresampled() instead of imagecopyresized() to create better-looking thumbnails
$config['image']['jpeg_quality']        = 75;        ///< 0-100% quality for recompression of very large uploads (like digital camera pictures)

$predef_color['white'] = array(255,255,255);
$predef_color['black'] = array(0,0,0);

/**
 * Crops selected image to the requested dimensions
 *
 * @param $in_file filename of input image
 * @param $in_file filename of output image
 * @param $x1 coordinate x1
 * @param $y1 coordinate y1
 * @param $x2 coordinate x2
 * @param $y2 coordinate y2
 * @return true on success
 */
function cropImage($in_file, $out_file, $x1, $y1, $x2, $y2)
{
    global $h, $config;
    if (!is_numeric($x1) || !is_numeric($y1) || !is_numeric($x2) || !is_numeric($y2)) return false;

    $mime = $h->files->lookupMimeType($in_file);

    if (!$h->files->image_convert) return false;

    $crop = ($x2-$x1).'x'.($y2-$y1).'+'.$x1.'+'.$y1;

    //Crop with imagemagick
    switch ($mime) {
        case 'image/jpeg':
            $c = 'convert -crop '.$crop. ' -quality '.$config['image']['jpeg_quality'].' '.escapeshellarg($in_file).' JPG:'.escapeshellarg($out_file);
            break;

        case 'image/png':
            $c = 'convert -crop '.$crop. ' '.escapeshellarg($in_file).' PNG:'.escapeshellarg($out_file);
            break;

        case 'image/gif':
            $c = 'convert -crop '.$crop. ' '.escapeshellarg($in_file).' GIF:'.escapeshellarg($out_file);
            break;

        default:
            echo 'cropImage(): Unhandled mimetype "'.$mime.'"<br/>';
            return false;
    }
    //echo 'Executing: '.$c.'<br/>';
    exec($c);
    if (!file_exists($out_file)) return false;
    return true;
}

/**
 * Converts a image to specified file type. Currently supports conversions to jpeg, png or gif
 * Requires ImageMagick commandline image converter "convert" installed
 *
 * @param $in_file input filename
 * @param $out_file output filename
 * @param $to_mime wanted output format
 * @return true on success
 */
function convertImage($in_file, $out_file, $to_mime)
{
    global $config;

    switch ($to_mime) {
        case 'image/jpeg':
            $c = 'convert -quality '.$config['image']['jpeg_quality'].' '.escapeshellarg($in_file).' JPG:'.escapeshellarg($out_file);
            break;

        case 'image/png':
            $c = 'convert '.escapeshellarg($in_file).' PNG:'.escapeshellarg($out_file);
            break;

        case 'image/gif':
            $c = 'convert '.escapeshellarg($in_file).' GIF:'.escapeshellarg($out_file);
            break;

        default:
            echo 'convertImage(): Unhandled mimetype "'.$to_mime.'"<br/>';
            return false;
    }
    //echo 'Executing: '.$c.'<br/>';
    exec($c);
    if (!file_exists($out_file)) return false;
    return true;
}

/**
 * Rotates a image the specified angle. Uses imagemagick if possible
 * The gd function imagerotate() is only available in bundled gd (php windows)
 *
 * @param $in_file input filename
 * @param $out_file output filename
 * @param $_angle %angle to rotate. between -360 and 360
 */
function rotateImage($in_file, $out_file, $_angle)
{
    global $h, $config;
    if (!is_numeric($_angle)) return false;

    $mime = $h->files->lookupMimeType($in_file);

    if ($h->files->image_convert) {
        //Rotate with imagemagick
        switch ($mime) {
            case 'image/jpeg':
                $c = 'convert -rotate '.$_angle. ' -quality '.$config['image']['jpeg_quality'].' '.escapeshellarg($in_file).' JPG:'.escapeshellarg($out_file);
                break;

            case 'image/png':
                $c = 'convert -rotate '.$_angle. ' '.escapeshellarg($in_file).' PNG:'.escapeshellarg($out_file);
                break;

            case 'image/gif':
                $c = 'convert -rotate '.$_angle. ' '.escapeshellarg($in_file).' GIF:'.escapeshellarg($out_file);
                break;

            default:
                echo 'rotateImage(): Unhandled mimetype "'.$mime.'"<br/>';
                return false;
        }
        //echo 'Executing: '.$c.'<br/>';
        exec($c);
        if (!file_exists($out_file)) return false;
        return true;
    }

    if (!function_exists('imagerotate')) {
        die('CANNOT ROTATE IMAGE. PLEASE INSTALL IMAGEMAGICK OR BUNDLED PHP_GD!');
    }

    switch ($mime) {
        case 'image/jpeg': $im = imagecreatefromjpeg($in_file); break;
        case 'image/png':  $im = imagecreatefrompng($in_file); break;
        case 'image/gif':  $im = imagecreatefromgif($in_file); break;
        default: die('Unsupported image type '.$mime);
    }

    $rotated = imagerotate($im, $_angle, 0);
    imagedestroy($im);

    switch ($mime) {
        case 'image/jpeg': imagejpeg($rotated, $filename, $config['image']['jpeg_quality']); break;
        case 'image/png':  imagepng($rotated, $filename); break;
        case 'image/gif':  imagegif($rotated, $filename); break;
        default: die('Unsupported image type '.$mime);
    }

    imagedestroy($rotated);
}

/**
 * Loads a font & sets font type & font height variables
 */
function loadFont($str, $font, $ttf_size, $ttf_angle, &$ttf, &$fh)
{
    $ttf = false;
    if (!is_numeric($font)) {
        if (substr(strtolower($font), -4) == '.ttf' || substr(strtolower($font), -4) == '.otf') {
            //supported font formats:
            //.ttf (true type font)
            //.otf (open type font)
            $ttf = true;

            $fh = 0;
            foreach ($str as $txt) {    //find highest font height
                $x = imagettfbbox($ttf_size, $ttf_angle, $font, $txt);
                $t = $x[1] - $x[7];
                if ($t > $fh) $fh = $t;
            }
        } else {
            //GDF font handling
            $font = imageloadfont($font);
        }
    }

    if (!$ttf) $fh = imagefontheight($font);

    return $font;
}

/**
 * Draws text centered horizontally & vertically
 *
 * @param $str array of lines of text to print
 * @param $template png image to use as template to draw the text upon
 * @param $font specify the font to use. numeric 1-5 for gd's internal fonts, or specify a .gdf or .ttf font instead
 * @param $col optional color to draw the font in, array(r,g,b). defaults to black
 * @param $ttf_size optional size of ttf font, defaults to 12
 * @return image resource
 */
function pngCenterText($str, $template, $font = 1, $col = array(), $ttf_size = 12)
{
    $ttf_angle = 0;

    $im = imagecreatefrompng($template);

    if (empty($col)) {
        $color = imagecolorallocate($im, 0, 0, 0); //defaults to black
    } else {
        $color = imagecolorallocate($im, $col[0], $col[1], $col[2]);
    }

    $font = loadFont($str, $font, $ttf_size, $ttf_angle, &$ttf, &$fh);

    $i = 0;

    //Prints the text in $str array centered vertically & horizontally over the image
    foreach ($str as $txt) {
        if (!$ttf) {
            $txt = mb_convert_encoding($txt, 'ISO-8859-1', 'auto'); //FIXME required with php 5.2, as imagestring() cant handle utf8
            $fw = strlen($txt) * imagefontwidth($font);
        } else {
            $x = imagettfbbox($ttf_size, $ttf_angle, $font, $txt);
            $fw = $x[2] - $x[0];    //font width
        }

        $px = (imagesx($im) / 2) - ($fw / 2);
        $py = (imagesy($im) / 2) - ( ((count($str)/2) - $i) * $fh);

        if (!$ttf) {
            imagestring($im, $font, $px, $py, $txt, $color);
        } else {
            imagettftext($im, $ttf_size, $ttf_angle, $px, $py + $fh, $color, $font, $txt);
        }

        $i++;
    }
    return $im;
}

function pngLeftText($str, $template, $font = 1, $col = array(), $ttf_size = 12, $px = 10, $py = 10)
{
    $ttf_angle = 0;

    $im = imagecreatefrompng($template);

    if (empty($col)) {
        $color = imagecolorallocate($im, 0, 0, 0); //defaults to black
    } else {
        $color = imagecolorallocate($im, $col[0], $col[1], $col[2]);
    }

    $font = loadFont($str, $font, $ttf_size, $ttf_angle, &$ttf, &$fh);

    //Prints the text in $str array centered vertically & horizontally over the image
    foreach ($str as $txt) {
        if (!$ttf) {
            $txt = mb_convert_encoding($txt, 'ISO-8859-1', 'auto'); //FIXME required with php 5.2, as imagestring() cant handle utf8
        }

        $tmp_color = array();
        $p1 = strpos($txt, '[');
        $p2 = strpos($txt, ']');
        if ($p1 !== false && $p2 !== false && $p2 > $p1) {
            //extract RGB color code tag & use for current row only, format: [r,g,b]
            $cut = explode(',', substr($txt, $p1 +1, $p2-$p1-1));
            $tmp_color = imagecolorallocate($im, $cut[0], $cut[1], $cut[2]);
            $txt = substr($txt, $p2 +1);
        }

        $py += $fh;

        if (!$ttf) {
            imagestring($im, $font, $px, $py, $txt, empty($tmp_color) ? $color : $tmp_color);
        } else {
            imagettftext($im, $ttf_size, $ttf_angle, $px, $py, empty($tmp_color) ? $color : $tmp_color, $font, $txt);
        }
    }
    return $im;
}

/**
 * Wrapper for GD imagecreatefrom*() functions
 */
function loadImage($in_file)
{
    $info = getimagesize($in_file);
    if (!$info) return false;

    switch ($info['mime']) {
        case 'image/jpeg':
            return imagecreatefromjpeg($in_file);

        case 'image/png':
            return imagecreatefrompng($in_file);

        case 'image/gif':
            return imagecreatefromgif($in_file);

        default:
            echo "Unknown image type: ".$info['mime']."\n";
            return false;
    }
}

?>
