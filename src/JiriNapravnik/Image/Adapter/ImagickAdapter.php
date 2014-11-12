<?php

namespace JiriNapravnik\Image\Adapter;

use Nette;
use Nette\InvalidArgumentException;
use JiriNapravnik\Image;
use Imagick;

/**
 * Basic manipulation with images via Imagick extension.
 * 
 * @author     Jiří Nápravník (http://jirinapravnik.cz)
 * 
 * Full copyright and licenses in the file license.md * 
 */

class ImagickAdapter extends AdapterAbstract
{

	private $saveMetadata;

	public static function fromFile($file, &$format = NULL)
	{
		if (!extension_loaded('imagick')) {
			throw new NotSupportedException("PHP extension Imagick is not loaded.");
		}

		$imageResource = new Imagick($file);
		return new static($imageResource);
	}

	public static function fromBlank($width, $height, $color = NULL)
	{
		if (!extension_loaded('imagick')) {
			throw new NotSupportedException("PHP extension Imagick is not loaded.");
		}

		if(is_array($color)){
			$color = 'rgba(' . $color['red'] . ',' . $color['green'] . ',' . $color['blue'] . ',' . $color['alpha'] . ')';
		}
		
		$image = new Imagick();
		$image->newimage($width, $height, new \ImagickPixel($color));
		
		return new static($image);
	}

	public function __construct($imageResource)
	{
		$this->setImageResource($imageResource);
	}

	protected function setImageResource($image)
	{
		if (!$image instanceof Imagick) {
			throw new InvalidArgumentException('Image is not valid.');
		}
		$this->image = $image;
	}

	public function resize($width, $height, $flags = Image::FIT)
	{
		if ($flags & Image::EXACT) {
			return $this->resize($width, $height, Image::FILL | Image::SHRINK_ONLY)
				->crop('50%', '50%', $width, $height);
		}

		list($newWidth, $newHeight) = Image::calculateSize($this->getWidth(), $this->getHeight(), $width, $height, $flags);

		$this->getImageResource()->resizeImage($newWidth, $newHeight, IMAGICK::FILTER_LANCZOS, 1);

		return $this;
	}

	public function crop($left, $top, $width, $height)
	{
		list($left, $top, $width, $height) = Image::calculateCutout($this->getWidth(), $this->getHeight(), $left, $top, $width, $height);
		$this->getImageResource()->cropImage($width, $height, $left, $top);
		return $this;
	}

	public function sharpen()
	{
		$this->getImageResource()->sharpenImage(5, 1);
		return $this;
	}

	public function save($file = NULL, $quality = 85, $type = NULL)
	{
		if ($type === NULL) {
			$type = strtolower(pathinfo($file, PATHINFO_EXTENSION));
		} else {
			switch ($type) {
				case Image::JPEG:
					$type = 'jpeg';
					break;
				case Image::PNG:
					$type = 'png';
					break;
				case Image::GIF:
					$type = 'gif';
			}
		}


		$im = $this->getImageResource();
		$im->setImageFormat($type);
		$im->setImageCompressionQuality($quality);

		if ($this->getSaveMetadata() === FALSE) {
			$profiles = $im->getImageProfiles();
			$im->stripImage();
			foreach ($profiles as $key => $p) {
				if (in_array($key, array('icc', 'icm'))) {
					$im->setImageProfile($key, $p);
				}
			}
		}

		if (strlen($file) > 0) {
			$im->writeImage($file);
		} else {
			echo $this->getImageResource();
		}
		return TRUE;
	}

	public function place(IImageAdapter $image, $left = 0, $top = 0, $opacity = 100)
	{
		$opacity = max(0, min(100, (int) $opacity));
		
		if (substr($left, -1) === '%') {
			$left = round(($this->getWidth() - $image->getWidth()) / 100 * $left);
		}

		if (substr($top, -1) === '%') {
			$top = round(($this->getHeight() - $image->getHeight()) / 100 * $top);
		}

		//$image->getImageResource()->setImageOpacity($opacity / 100);
		$this->getImageResource()->evaluateImage(Imagick::EVALUATE_MULTIPLY, $opacity / 100, Imagick::CHANNEL_ALPHA);
		$this->getImageResource()->compositeImage($image->getImageResource(), Imagick::COMPOSITE_OVER, $left, $top);
		return $this;
	}

	public function getHeight()
	{
		$arr = $this->getImageResource()->getImageGeometry();
		return $arr['height'];
	}

	public function getWidth()
	{
		$arr = $this->getImageResource()->getImageGeometry();
		return $arr['width'];
	}

	public function getSaveMetadata()
	{
		return $this->saveMetadata;
	}

	public function setSaveMetadata($saveMetadata)
	{
		$this->saveMetadata = $saveMetadata;
		return $this;
	}

	/**
	 * Call to undefined method.
	 *
	 * @param  string  method name
	 * @param  array   arguments
	 * @return mixed
	 * @throws MemberAccessException
	 */
	public function __call($method, $args)
	{
		if (method_exists($this->getImageResource(), $method)) {
			$callback = callback($this->getImageResource(), $method);
			$result = $callback->invokeArgs($args);
			if(is_bool($result)){
				return $this;
			} else {
				return $result;
			}
		}
		return parent::__call($method, $args);
	}
}
