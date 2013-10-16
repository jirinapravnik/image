<?php

namespace JiriNapravnik\Image\Adapter;

use JiriNapravnik\Image;
use Nette\InvalidArgumentException;
use Nette\MemberAccessException;
use Nette\NotSupportedException;
use Nette\UnknownImageFileException;

/**
 * Basic manipulation with images via GD extension.
 * 
 * @author     David Grudl (http://davidgrudl.com) - original Nette\Image from Nette Framework (http://nette.org)
 * @author     Jiří Nápravník (http://jirinapravnik.cz) - Imagick adapter and refactoring
 * 
 * Full copyright and licenses in the file license.md
 * 
 *
 * @method void alphaBlending(bool $on)
 * @method void antialias(bool $on)
 * @method void arc($x, $y, $w, $h, $s, $e, $color)
 * @method void char($font, $x, $y, $char, $color)
 * @method void charUp($font, $x, $y, $char, $color)
 * @method int colorAllocate($red, $green, $blue)
 * @method int colorAllocateAlpha($red, $green, $blue, $alpha)
 * @method int colorAt($x, $y)
 * @method int colorClosest($red, $green, $blue)
 * @method int colorClosestAlpha($red, $green, $blue, $alpha)
 * @method int colorClosestHWB($red, $green, $blue)
 * @method void colorDeallocate($color)
 * @method int colorExact($red, $green, $blue)
 * @method int colorExactAlpha($red, $green, $blue, $alpha)
 * @method void colorMatch(Image $image2)
 * @method int colorResolve($red, $green, $blue)
 * @method int colorResolveAlpha($red, $green, $blue, $alpha)
 * @method void colorSet($index, $red, $green, $blue)
 * @method array colorsForIndex($index)
 * @method int colorsTotal()
 * @method int colorTransparent([$color])
 * @method void convolution(array $matrix, float $div, float $offset)
 * @method void copy(Image $src, $dstX, $dstY, $srcX, $srcY, $srcW, $srcH)
 * @method void copyMerge(Image $src, $dstX, $dstY, $srcX, $srcY, $srcW, $srcH, $opacity)
 * @method void copyMergeGray(Image $src, $dstX, $dstY, $srcX, $srcY, $srcW, $srcH, $opacity)
 * @method void copyResampled(Image $src, $dstX, $dstY, $srcX, $srcY, $dstW, $dstH, $srcW, $srcH)
 * @method void copyResized(Image $src, $dstX, $dstY, $srcX, $srcY, $dstW, $dstH, $srcW, $srcH)
 * @method void dashedLine($x1, $y1, $x2, $y2, $color)
 * @method void ellipse($cx, $cy, $w, $h, $color)
 * @method void fill($x, $y, $color)
 * @method void filledArc($cx, $cy, $w, $h, $s, $e, $color, $style)
 * @method void filledEllipse($cx, $cy, $w, $h, $color)
 * @method void filledPolygon(array $points, $numPoints, $color)
 * @method void filledRectangle($x1, $y1, $x2, $y2, $color)
 * @method void fillToBorder($x, $y, $border, $color)
 * @method void filter($filtertype [, ...])
 * @method int fontHeight($font)
 * @method int fontWidth($font)
 * @method array ftBBox($size, $angle, string $fontFile, string $text [, array $extrainfo])
 * @method array ftText($size, $angle, $x, $y, $col, string $fontFile, string $text [, array $extrainfo])
 * @method void gammaCorrect(float $inputgamma, float $outputgamma)
 * @method int interlace([$interlace])
 * @method bool isTrueColor()
 * @method void layerEffect($effect)
 * @method void line($x1, $y1, $x2, $y2, $color)
 * @method int loadFont(string $file)
 * @method void paletteCopy(Image $source)
 * @method void polygon(array $points, $numPoints, $color)
 * @method array psBBox(string $text, $font, $size [, $space] [, $tightness] [, float $angle])
 * @method void psEncodeFont($fontIndex, string $encodingfile)
 * @method void psExtendFont($fontIndex, float $extend)
 * @method void psFreeFont($fontindex)
 * @method resource psLoadFont(string $filename)
 * @method void psSlantFont($fontIndex, float $slant)
 * @method array psText(string $text, $font, $size, $color, $backgroundColor, $x, $y [, $space] [, $tightness] [, float $angle] [, $antialiasSteps])
 * @method void rectangle($x1, $y1, $x2, $y2, $col)
 * @method Image rotate(float $angle, $backgroundColor)
 * @method void saveAlpha(bool $saveflag)
 * @method void setBrush(Image $brush)
 * @method void setPixel($x, $y, $color)
 * @method void setStyle(array $style)
 * @method void setThickness($thickness)
 * @method void setTile(Image $tile)
 * @method void string($font, $x, $y, string $s, $col)
 * @method void stringUp($font, $x, $y, string $s, $col)
 * @method void trueColorToPalette(bool $dither, $ncolors)
 * @method array ttfBBox($size, $angle, string $fontfile, string $text)
 * @method array ttfText($size, $angle, $x, $y, $color, string $fontfile, string $text)
 * @method int types()
 * @property-read int $width
 * @property-read int $height
 * @property-read resource $imageResource
 */
class GdAdapter extends AdapterAbstract
{

	public static function fromFile($file, & $format = NULL)
	{
		if (!extension_loaded('gd')) {
			throw new NotSupportedException("PHP extension GD is not loaded.");
		}

		$info = @getimagesize($file); // @ - files smaller than 12 bytes causes read error

		switch ($format = $info[2]) {
			case Image::JPEG:
				return new static(imagecreatefromjpeg($file));

			case Image::PNG:
				return new static(imagecreatefrompng($file));

			case Image::GIF:
				return new static(imagecreatefromgif($file));

			default:
				throw new UnknownImageFileException("Unknown image type or file '$file' not found.");
		}
	}

	public static function fromBlank($width, $height, $color = NULL)
	{
		if (!extension_loaded('gd')) {
			throw new NotSupportedException("PHP extension GD is not loaded.");
		}

		$width = (int) $width;
		$height = (int) $height;
		if ($width < 1 || $height < 1) {
			throw new InvalidArgumentException('Image width and height must be greater than zero.');
		}

		$image = imagecreatetruecolor($width, $height);
		if (is_array($color)) {
			$color += array('alpha' => 0);
			$color = imagecolorallocatealpha($image, $color['red'], $color['green'], $color['blue'], $color['alpha']);
			imagealphablending($image, FALSE);
			imagefilledrectangle($image, 0, 0, $width - 1, $height - 1, $color);
			imagealphablending($image, TRUE);
		}
		return new static($image);
	}

	public function __construct($image)
	{
		$this->setImageResource($image);
		imagesavealpha($image, TRUE);
	}

	protected function setImageResource($image)
	{
		if (!is_resource($image) || get_resource_type($image) !== 'gd') {
			throw new InvalidArgumentException('Image is not valid.');
		}
		$this->image = $image;
		return $this;
	}

	public function resize($width, $height, $flags = Image::FIT)
	{
		if ($flags & Image::EXACT) {
			return $this->resize($width, $height, Image::FILL)->crop('50%', '50%', $width, $height);
		}

		list($newWidth, $newHeight) = Image::calculateSize($this->getWidth(), $this->getHeight(), $width, $height, $flags);

		if ($newWidth !== $this->getWidth() || $newHeight !== $this->getHeight()) { // resize
			$newImage = static::fromBlank($newWidth, $newHeight, Image::RGB(0, 0, 0, 127))->getImageResource();
			imagecopyresampled(
				$newImage, $this->getImageResource(), 0, 0, 0, 0, $newWidth, $newHeight, $this->getWidth(), $this->getHeight()
			);
			$this->image = $newImage;
		}

		if ($width < 0 || $height < 0) { // flip is processed in two steps for better quality
			$newImage = static::fromBlank($newWidth, $newHeight, Image::RGB(0, 0, 0, 127))->getImageResource();
			imagecopyresampled(
				$newImage, $this->getImageResource(), 0, 0, $width < 0 ? $newWidth - 1 : 0, $height < 0 ? $newHeight - 1 : 0, $newWidth, $newHeight, $width < 0 ? -$newWidth : $newWidth, $height < 0 ? -$newHeight : $newHeight
			);
			$this->image = $newImage;
		}
		return $this;
	}

	public function crop($left, $top, $width, $height)
	{
		list($left, $top, $width, $height) = Image::calculateCutout($this->getWidth(), $this->getHeight(), $left, $top, $width, $height);
		$newImage = static::fromBlank($width, $height, Image::RGB(0, 0, 0, 127))->getImageResource();
		imagecopy($newImage, $this->getImageResource(), 0, 0, $left, $top, $width, $height);
		$this->image = $newImage;
		return $this;
	}

	public function sharpen()
	{
		imageconvolution($this->getImageResource(), array(// my magic numbers ;)
			array(-1, -1, -1),
			array(-1, 24, -1),
			array(-1, -1, -1),
			), 16, 0);
		return $this;
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

		if ($opacity === 100) {
			imagecopy(
				$this->getImageResource(), $image->getImageResource(), $left, $top, 0, 0, $image->getWidth(), $image->getHeight()
			);
		} elseif ($opacity <> 0) {
			imagecopymerge(
				$this->getImageResource(), $image->getImageResource(), $left, $top, 0, 0, $image->getWidth(), $image->getHeight(), $opacity
			);
		}
		return $this;
	}

	public function save($file = NULL, $quality = NULL, $type = NULL)
	{
		if ($type === NULL) {
			switch (strtolower(pathinfo($file, PATHINFO_EXTENSION))) {
				case 'jpg':
				case 'jpeg':
					$type = Image::JPEG;
					break;
				case 'png':
					$type = Image::PNG;
					break;
				case 'gif':
					$type = Image::GIF;
			}
		}

		switch ($type) {
			case Image::JPEG:
				$quality = $quality === NULL ? 85 : max(0, min(100, (int) $quality));
				return imagejpeg($this->getImageResource(), $file, $quality);

			case Image::PNG:
				$quality = $quality === NULL ? 9 : max(0, min(9, (int) $quality));
				return imagepng($this->getImageResource(), $file, $quality);

			case Image::GIF:
				return imagegif($this->getImageResource(), $file);

			default:
				throw new InvalidArgumentException("Unsupported image type.");
		}
	}

	public function getWidth()
	{
		return imagesx($this->image);
	}

	public function getHeight()
	{
		return imagesy($this->image);
	}

	/**
	 * Call to undefined method.
	 *
	 * @param  string  method name
	 * @param  array   arguments
	 * @return mixed
	 * @throws MemberAccessException
	 */
	public function __call($name, $args)
	{
		$function = 'image' . $name;
		if (function_exists($function)) {
			foreach ($args as $key => $value) {
				if ($value instanceof self) {
					$args[$key] = $value->getImageResource();
				} elseif (is_array($value) && isset($value['red'])) { // rgb
					$args[$key] = imagecolorallocatealpha(
						$this->getImageResource(), $value['red'], $value['green'], $value['blue'], $value['alpha']
					);
				}
			}
			array_unshift($args, $this->getImageResource());

			$res = call_user_func_array($function, $args);
			return is_resource($res) && get_resource_type($res) === 'gd' ? $this->setImageResource($res) : $res;
		}

		return parent::__call($name, $args);
	}

}
