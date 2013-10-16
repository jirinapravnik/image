<?php

/**
 * Abstract image adapter
 *  
 * @author     David Grudl (http://davidgrudl.com) - original Nette\Image from Nette Framework (http://nette.org)
 * @author     Jiří Nápravník (http://jirinapravnik.cz) - Imagick adapter and refactoring
 * 
 * Full copyright and licenses in the file license.md
 */

namespace JiriNapravnik\Image\Adapter;

use Nette;
use JiriNapravnik\Image;
use JiriNapravnik\Image\Adapter\IImageAdapter;

abstract class AdapterAbstract extends Nette\Object implements IImageAdapter
{

	/**
	 * @var image resource
	 */
	protected $image;

	public function getImageResource()
	{
		return $this->image;
	}

	/**
	 * Sets image resource.
	 * @param  resource
	 * @return self
	 */
	protected abstract function setImageResource($image);

	public function send($type = Image::JPEG, $quality = NULL)
	{
		if ($type !== Image::GIF && $type !== Image::PNG && $type !== Image::JPEG) {
			throw new InvalidArgumentException("Unsupported image type.");
		}
		header('Content-Type: ' . image_type_to_mime_type($type));
		return $this->save(NULL, $quality, $type);
	}

	public function toString($type = Image::JPEG, $quality = NULL)
	{
		ob_start();
		$this->save(NULL, $quality, $type);
		return ob_get_clean();
	}

	public function __toString()
	{
		try {
			return $this->toString();
		} catch (\Exception $e) {
			trigger_error("Exception in " . __METHOD__ . "(): {$e->getMessage()} in {$e->getFile()}:{$e->getLine()}", E_USER_ERROR);
		}
	}

}
