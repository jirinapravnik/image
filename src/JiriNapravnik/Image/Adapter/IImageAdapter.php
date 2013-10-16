<?php

/**
 * Interface for image adapters
 * 
 * @author     Jiří Nápravník (http://jirinapravnik.cz) - Imagick adapter and refactoring
 * 
 * Full copyright and licenses in the file license.md
 */

namespace JiriNapravnik\Image\Adapter;

interface IImageAdapter
{

	/**
	 * Opens image from file.
	 * @param  string
	 * @param  mixed  detected image format
	 * @return Image
	 */
	public static function fromFile($file, & $format = NULL);

	/**
	 * Creates blank image.
	 * @param  int
	 * @param  int
	 * @param  array
	 * @return Image
	 */
	public static function fromBlank($width, $height, $color = NULL);

	/**
	 * Returns image Imagick resource.
	 * @return image resource/object
	 */
	public function getImageResource();

	/**
	 * Returns image width.
	 * @return int
	 */
	public function getWidth();

	/**
	 * Returns image height.
	 * @return int
	 */
	public function getHeight();

	/**
	 * Resizes image.
	 * @param  mixed  width in pixels or percent
	 * @param  mixed  height in pixels or percent
	 * @param  int    flags
	 * @return self
	 */
	public function resize($width, $height, $flags = self::FIT);

	/**
	 * Crops image.
	 * @param  mixed  x-offset in pixels or percent
	 * @param  mixed  y-offset in pixels or percent
	 * @param  mixed  width in pixels or percent
	 * @param  mixed  height in pixels or percent
	 * @return self
	 */
	public function crop($left, $top, $width, $height);

	/**
	 * Sharpen image.
	 * @return self
	 */
	public function sharpen();

	/**
	 * Puts another image into this image.
	 * @param  Image
	 * @param  mixed  x-coordinate in pixels or percent
	 * @param  mixed  y-coordinate in pixels or percent
	 * @param  int  opacity 0..100
	 * @return self
	 */
	public function place(IImageAdapter $image, $left = 0, $top = 0, $opacity = 100);

	/**
	 * Saves image to the file.
	 * @param  string  filename
	 * @param  int  quality 0..100 (for JPEG and PNG)
	 * @param  int  optional image type
	 * @return bool TRUE on success or FALSE on failure.
	 */
	public function save($file = NULL, $quality = NULL, $type = NULL);

	/**
	 * Outputs image to browser.
	 * @param  int  image type
	 * @param  int  quality 0..100 (for JPEG and PNG)
	 * @return bool TRUE on success or FALSE on failure.
	 */
	public function send($type = Image::JPEG, $quality = NULL);

	/**
	 * Outputs image to string.
	 * @param  int  image type
	 * @param  int  quality 0..100 (for JPEG and PNG)
	 * @return string
	 */
	public function toString($type = Image::JPEG, $quality = NULL);

	/**
	 * Outputs image to string.
	 * @return string
	 */
	public function __toString();
}
