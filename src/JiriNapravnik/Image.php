<?php

/**
 * Image
 * 
 * @author     David Grudl (http://davidgrudl.com) - original Nette\Image from Nette Framework (http://nette.org)
 * @author     Jiří Nápravník (http://jirinapravnik.cz) - Imagick adapter and refactoring
 * 
 * Full copyright and licenses in the file license.md
 */

namespace JiriNapravnik;

class Image
{

	/** only shrinks images */
	const SHRINK_ONLY = 1;

	/** will ignore aspect ratio */
	const STRETCH = 2;

	/** fits in given area so its dimensions are less than or equal to the required dimensions */
	const FIT = 0;

	/** fills given area so its dimensions are greater than or equal to the required dimensions */
	const FILL = 4;

	/** fills given area exactly */
	const EXACT = 8;

	/** @int image types {@link send()} */
	const JPEG = IMAGETYPE_JPEG,
		PNG = IMAGETYPE_PNG,
		GIF = IMAGETYPE_GIF;

	private static $adapter = null;

	/**
	 * create image from file
	 * @param type $file
	 * @param type $format
	 * @return Enbros\Image\Adapter\IImageAdapter
	 */
	public static function fromFile($file, & $format = NULL)
	{
		$adapter = callback(self::$adapter, 'fromFile');
		return $adapter->invokeArgs(func_get_args());
	}

	/**
	 * 
	 * @param type $width
	 * @param type $height
	 * @param type $color
	 */
	public static function fromBlank($width, $height, $color = NULL)
	{
		if (
			self::$adapter instanceof Image\Adapter\ImagickAdapter &&
			is_array($color) &&
			isset($color['red']) && isset($color['green']) && isset($color['green'])) {
			$color = 'rgba(' . $color['red'] . ', ' . $color['green'] . ', ' . $color['blue'] . ', ' . $color['alpha'] . ')';
		}
		
		$adapter = callback(self::$adapter, 'fromBlank');
		return $adapter->invokeArgs(array($width, $height, $color));
	}

	/**
	 * Returns RGB color.
	 * @param  int  red 0..255
	 * @param  int  green 0..255
	 * @param  int  blue 0..255
	 * @param  int  transparency 0..127
	 * @return array
	 */
	public static function rgb($red, $green, $blue, $transparency = 0)
	{
		return array(
			'red' => max(0, min(255, (int) $red)),
			'green' => max(0, min(255, (int) $green)),
			'blue' => max(0, min(255, (int) $blue)),
			'alpha' => max(0, min(127, (int) $transparency)),
		);
	}

	/**
	 * Get format from the image stream in the string.
	 * @param  string
	 * @return mixed  detected image format
	 */
	public static function getFormatFromString($s)
	{
		$types = array('image/jpeg' => self::JPEG, 'image/gif' => self::GIF, 'image/png' => self::PNG);
		$type = Utils\MimeTypeDetector::fromString($s);
		return isset($types[$type]) ? $types[$type] : NULL;
	}

	/**
	 * Calculates dimensions of resized image.
	 * @param  mixed  source width
	 * @param  mixed  source height
	 * @param  mixed  width in pixels or percent
	 * @param  mixed  height in pixels or percent
	 * @param  int    flags
	 * @return array
	 */
	public static function calculateSize($srcWidth, $srcHeight, $newWidth, $newHeight, $flags = self::FIT)
	{
		if (substr($newWidth, -1) === '%') {
			$newWidth = round($srcWidth / 100 * abs($newWidth));
			$percents = TRUE;
		} else {
			$newWidth = (int) abs($newWidth);
		}

		if (substr($newHeight, -1) === '%') {
			$newHeight = round($srcHeight / 100 * abs($newHeight));
			$flags |= empty($percents) ? 0 : self::STRETCH;
		} else {
			$newHeight = (int) abs($newHeight);
		}

		if ($flags & self::STRETCH) { // non-proportional
			if (empty($newWidth) || empty($newHeight)) {
				throw new InvalidArgumentException('For stretching must be both width and height specified.');
			}

			if ($flags & self::SHRINK_ONLY) {
				$newWidth = round($srcWidth * min(1, $newWidth / $srcWidth));
				$newHeight = round($srcHeight * min(1, $newHeight / $srcHeight));
			}
		} else {  // proportional
			if (empty($newWidth) && empty($newHeight)) {
				throw new InvalidArgumentException('At least width or height must be specified.');
			}

			$scale = array();
			if ($newWidth > 0) { // fit width
				$scale[] = $newWidth / $srcWidth;
			}

			if ($newHeight > 0) { // fit height
				$scale[] = $newHeight / $srcHeight;
			}

			if ($flags & self::FILL) {
				$scale = array(max($scale));
			}

			if ($flags & self::SHRINK_ONLY) {
				$scale[] = 1;
			}

			$scale = min($scale);
			$newWidth = round($srcWidth * $scale);
			$newHeight = round($srcHeight * $scale);
		}

		return array(max((int) $newWidth, 1), max((int) $newHeight, 1));
	}

	/**
	 * Calculates dimensions of cutout in image.
	 * @param  mixed  source width
	 * @param  mixed  source height
	 * @param  mixed  x-offset in pixels or percent
	 * @param  mixed  y-offset in pixels or percent
	 * @param  mixed  width in pixels or percent
	 * @param  mixed  height in pixels or percent
	 * @return array
	 */
	public static function calculateCutout($srcWidth, $srcHeight, $left, $top, $newWidth, $newHeight)
	{
		if (substr($newWidth, -1) === '%') {
			$newWidth = round($srcWidth / 100 * $newWidth);
		}
		if (substr($newHeight, -1) === '%') {
			$newHeight = round($srcHeight / 100 * $newHeight);
		}
		if (substr($left, -1) === '%') {
			$left = round(($srcWidth - $newWidth) / 100 * $left);
		}
		if (substr($top, -1) === '%') {
			$top = round(($srcHeight - $newHeight) / 100 * $top);
		}
		if ($left < 0) {
			$newWidth += $left;
			$left = 0;
		}
		if ($top < 0) {
			$newHeight += $top;
			$top = 0;
		}
		$newWidth = min((int) $newWidth, $srcWidth - $left);
		$newHeight = min((int) $newHeight, $srcHeight - $top);
		return array($left, $top, $newWidth, $newHeight);
	}

	public static function setAdapter($adapter)
	{
		self::$adapter = $adapter;
	}

	public static function getAdapter($adapter)
	{
		return self::$adapter;
	}

}

/**
 * The exception that indicates invalid image file.
 */
class UnknownImageFileException extends \Exception
{
	
}
