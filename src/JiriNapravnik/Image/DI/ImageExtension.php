<?php

namespace JiriNapravnik\Image\DI;

use Nette;
use Nette\PhpGenerator as Code;

/**
 * ImageExtension
 * 
 * @author     David Grudl (http://davidgrudl.com) - original Nette\Image from Nette Framework (http://nette.org)
 * @author     Jiří Nápravník (http://jirinapravnik.cz) - Imagick adapter and refactoring
 * 
 * Full copyright and licenses in the file license.md
 */
class ImageExtension extends Nette\DI\CompilerExtension
{

	/**
	 * @var array
	 */
	public $defaults = array(
		'adapter' => 'imagick',
		'imagickSaveMetadata' => FALSE,
		'imagickFilter' => 22,
	);

	
	public function afterCompile(Code\ClassType $class)
	{
		return;
		parent::afterCompile($class);

		$config = $this->getConfig($this->defaults);
		if($config['adapter'] === 'imagick'){
			$adapter = 'JiriNapravnik\Image\Adapter\ImagickAdapter';
		} else {
			$adapter = 'JiriNapravnik\Image\Adapter\GdAdapter';
		}
		
		$init = $class->methods['initialize'];
		$init->addBody('JiriNapravnik\Image::setAdapter(\'' . $adapter . '\');');
	}

	/**
	 * @param \Nette\Config\Configurator $configurator
	 */
	public static function register(Nette\Config\Configurator $configurator)
	{
		$configurator->onCompile[] = function ($config, Nette\Config\Compiler $compiler) {
			$compiler->addExtension('image', new ImageExtension());
		};
	}

}
