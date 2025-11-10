<?php

/**
 * Converts a PNG image to a .GRF file for use with Zebra printers
 *
 * The input is preferably a 1-bit black/white image but RGB images
 * are accepted as well.
 *
 * This function uses PHP's GD library image functions.
 *
 * @copyright Thomas Bruederli <inbox@brotherli.ch>
 *
 * @param string $filename Path to the input file
 * @param string $targetname Name of the GRF file reference
 * @return string ZPL command for uploading the graphic image (~DG)
 */
image2grf($filename='caddy.png');

function image2grf($filename, $targetname = 'R:IMAGE.GRF')
{
  
  $info = getimagesize($filename);
  $im = imagecreatefrompng($filename);

  $width = $info[0]; // imagesx($im);
  $height = $info[1]; // imagesy($im);
  $depth = $info['bits'] ?: 1;
  $threshold = $depth > 1 ? 160 : 0;
  $hexString = '';
  $byteShift = 7;
  $currentByte = 0;

  // iterate over all image pixels
  for ($y = 0; $y < $height; $y++) {
    for ($x = 0; $x < $width; $x++) {
      $color = imagecolorat($im, $x, $y);

      // compute gray value from RGB color
      if ($depth > 1) {
        $value = max($color >> 16, $color >> 8 & 255, $color & 255);
      } else {
        $value = $color;
      }

      // set (inverse) bit for the current pixel
      $currentByte |= (($value > $threshold ? 0 : 1) << $byteShift);
      $byteShift--;

      // 8 pixels filled one byte => append to output as hex
      if ($byteShift < 0) {
        $hexString .= sprintf('%02X', $currentByte);
        $currentByte = 0;
        $byteShift = 7;
      }
    }

    // append last byte at end of row
    if ($byteShift < 7) {
      $hexString .= sprintf('%02X', $currentByte);
      $currentByte = 0;
      $byteShift = 7;
    }

    $hexString .= PHP_EOL;
  }

  // compose ZPL ~DG command
  $totalBytes = ceil(($width * $height) / 8);
  $bytesPerRow = ceil($width / 8);
  return sprintf('~DG%s,%05d,%03d,' . PHP_EOL, $targetname, $totalBytes, $bytesPerRow) . $hexString;
}

// Usage:
// print image2grf($_SERVER['argv'][1], 'R:SAMPLE.GRF');
print "^XA^FO20,20^XGR:SAMPLE.GRF,1,1^FS^XZ" . PHP_EOL;