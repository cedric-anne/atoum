<?php

namespace mageekguy\atoum\tests\units\scripts\phar;

use
	mageekguy\atoum,
	mageekguy\atoum\mock,
	mageekguy\atoum\scripts\phar
;

require_once(__DIR__ . '/../../../runner.php');

class generator extends atoum\test
{
	public function testClassConstants()
	{
		$this->assert
			->string(phar\generator::phar)->isEqualTo('mageekguy.atoum.phar')
		;
	}

	public function test__construct()
	{
		$adapter = new atoum\test\adapter();

		$adapter->php_sapi_name = function() { return uniqid(); };

		$name = uniqid();

		$this->assert
			->exception(function() use ($name, $adapter) {
					$generator = new phar\generator($name, null, $adapter);
				}
			)
				->isInstanceOf('mageekguy\atoum\exceptions\logic')
				->hasMessage('\'' . $name . '\' must be used in CLI only')
		;

		$adapter->php_sapi_name = function() { return 'cli'; };

		$name = uniqid();

		$generator = new phar\generator($name, null, $adapter);

		$this->assert
			->object($generator->getLocale())->isInstanceOf('mageekguy\atoum\locale')
			->object($generator->getAdapter())->isInstanceOf('mageekguy\atoum\adapter')
			->object($generator->getOutputWriter())->isInstanceOf('mageekguy\atoum\writer')
			->object($generator->getErrorWriter())->isInstanceOf('mageekguy\atoum\writer')
			->string($generator->getName())->isEqualTo($name)
			->variable($generator->getOriginDirectory())->isNull()
			->variable($generator->getDestinationDirectory())->isNull()
			->object($generator->getArgumentsParser())->isInstanceOf('mageekguy\atoum\script\arguments\parser')
		;

		$name = uniqid();
		$locale = new atoum\locale();

		$generator = new phar\generator($name, $locale, $adapter);

		$this->assert
			->object($generator->getLocale())->isIdenticalTo($locale)
			->object($generator->getAdapter())->isIdenticalTo($adapter)
			->string($generator->getName())->isEqualTo($name)
			->variable($generator->getOriginDirectory())->isNull()
			->variable($generator->getDestinationDirectory())->isNull()
			->object($generator->getArgumentsParser())->isInstanceOf('mageekguy\atoum\script\arguments\parser')
		;
	}

	public function testSetOriginDirectory()
	{
		$adapter = new atoum\test\adapter();

		$adapter->php_sapi_name = function() { return 'cli'; };
		$adapter->realpath = function($path) { return $path; };

		$generator = new phar\generator(uniqid(), null, $adapter);

		$this->assert
			->exception(function() use ($generator) {
					$generator->setOriginDirectory('');
				}
			)
				->isInstanceOf('mageekguy\atoum\exceptions\runtime')
				->hasMessage('Empty origin directory is invalid')
		;

		$adapter->is_dir = function() { return false; };

		$directory = uniqid();

		$this->assert
			->exception(function() use ($generator, $directory) {
					$generator->setOriginDirectory($directory);
				}
			)
				->isInstanceOf('mageekguy\atoum\exceptions\runtime')
				->hasMessage('Path \'' . $directory . '\' of origin directory is invalid')
		;

		$adapter->is_dir = function() { return true; };

		$this->assert
			->object($generator->setOriginDirectory('/'))->isIdenticalTo($generator)
			->string($generator->getOriginDirectory())->isEqualTo('/')
		;

		$directory = uniqid();

		$this->assert
			->object($generator->setOriginDirectory($directory . '/'))->isIdenticalTo($generator)
			->string($generator->getOriginDirectory())->isEqualTo($directory)
		;

		$generator->setDestinationDirectory(uniqid());

		$this->assert
			->exception(function() use ($generator) {
					$generator->setOriginDirectory($generator->getDestinationDirectory());
				}
			)
				->isInstanceOf('mageekguy\atoum\exceptions\runtime')
				->hasMessage('Origin directory must be different from destination directory')
		;

		$realDirectory = $generator->getDestinationDirectory() . DIRECTORY_SEPARATOR . uniqid();

		$adapter->realpath = function($path) use ($realDirectory) { return $realDirectory; };

		$this->assert
			->object($generator->setOriginDirectory('/'))->isIdenticalTo($generator)
			->string($generator->getOriginDirectory())->isEqualTo($realDirectory)
		;
	}

	public function testSetDestinationDirectory()
	{
		$adapter = new atoum\test\adapter();

		$adapter->php_sapi_name = function() { return 'cli'; };
		$adapter->realpath = function($path) { return $path; };

		$generator = new phar\generator(uniqid(), null, $adapter);

		$this->assert
			->exception(function() use ($generator) {
					$generator->setDestinationDirectory('');
				}
			)
				->isInstanceOf('mageekguy\atoum\exceptions\runtime')
				->hasMessage('Empty destination directory is invalid')
		;

		$adapter->is_dir = function() { return false; };

		$directory = uniqid();

		$this->assert
			->exception(function() use ($generator, $directory) {
					$generator->setDestinationDirectory($directory);
				}
			)
				->isInstanceOf('mageekguy\atoum\exceptions\runtime')
				->hasMessage('Path \'' . $directory . '\' of destination directory is invalid')
		;

		$adapter->is_dir = function() { return true; };

		$this->assert
			->object($generator->setDestinationDirectory('/'))->isIdenticalTo($generator)
			->string($generator->getDestinationDirectory())->isEqualTo('/')
		;

		$directory = uniqid();

		$this->assert
			->object($generator->setDestinationDirectory($directory))->isIdenticalTo($generator)
			->string($generator->getDestinationDirectory())->isEqualTo($directory)
		;

		$directory = uniqid();

		$this->assert
			->object($generator->setDestinationDirectory($directory . '/'))->isIdenticalTo($generator)
			->string($generator->getDestinationDirectory())->isEqualTo($directory)
		;

		$generator->setOriginDirectory(uniqid());

		$this->assert
			->exception(function() use ($generator) {
					$generator->setDestinationDirectory($generator->getOriginDirectory());
				}
			)
				->isInstanceOf('mageekguy\atoum\exceptions\runtime')
				->hasMessage('Destination directory must be different from origin directory')
		;

		$realDirectory = $generator->getOriginDirectory() . DIRECTORY_SEPARATOR . uniqid();

		$adapter->realpath = function($path) use ($realDirectory) { return $realDirectory; };

		$this->assert
			->exception(function() use ($generator) {
					$generator->setDestinationDirectory(uniqid());
				}
			)
				->isInstanceOf('mageekguy\atoum\exceptions\runtime')
				->hasMessage('Origin directory must not include destination directory')
		;
	}

	public function testSetStubFile()
	{
		$adapter = new atoum\test\adapter();

		$adapter->php_sapi_name = function() { return 'cli'; };
		$adapter->realpath = function($path) { return $path; };

		$generator = new phar\generator(uniqid(), null, $adapter);

		$this->assert
			->exception(function() use ($generator) {
					$generator->setStubFile('');
				}
			)
				->isInstanceOf('mageekguy\atoum\exceptions\runtime')
				->hasMessage('Stub file is invalid')
		;

		$adapter->is_file = function() { return false; };

		$this->assert
			->exception(function() use ($generator) {
					$generator->setStubFile(uniqid());
				}
			)
				->isInstanceOf('mageekguy\atoum\exceptions\runtime')
				->hasMessage('Stub file is not a valid file')
		;

		$adapter->is_file = function() { return true; };

		$this->assert
			->object($generator->setStubFile($stubFile = uniqid()))->isIdenticalTo($generator)
			->string($generator->getStubFile())->isEqualTo($stubFile)
		;
	}

	public function testSetPharInjector()
	{
		$adapter = new atoum\test\adapter();

		$adapter->php_sapi_name = function() { return 'cli'; };
		$adapter->realpath = function($path) { return $path; };
		$adapter->is_dir = function() { return true; };

		$generator = new phar\generator(uniqid(), null, $adapter);

		$this->assert
			->exception(function() use ($generator) {
					$generator->setPharInjector(function() {});
				}
			)
				->isInstanceOf('mageekguy\atoum\exceptions\runtime')
				->hasMessage('Phar injector must take one argument')
		;

		$mockController = new mock\controller();
		$mockController
			->injectInNextMockInstance()
			->__construct = function() {}
		;

		$this->mock
			->generate('phar')
		;

		$pharName = uniqid();

		$phar = new \mock\phar($pharName);

		$this->assert
			->exception(function() use ($generator, $pharName) { $generator->getPhar($pharName); })
				->isInstanceOf('unexpectedValueException')
			->object($generator->setPharInjector(function($name) use ($phar) { return $phar; }))->isIdenticalTo($generator)
			->object($generator->getPhar(uniqid()))->isIdenticalTo($phar)
		;
	}

	public function testSetFileIteratorInjector()
	{
		$adapter = new atoum\test\adapter();

		$adapter->php_sapi_name = function() { return 'cli'; };
		$adapter->realpath = function($path) { return $path; };
		$adapter->is_dir = function() { return true; };

		$generator = new phar\generator(uniqid(), null, $adapter);

		$this->assert
			->exception(function() use ($generator) {
					$generator->setSrcIteratorInjector(function() {});
				}
			)
				->isInstanceOf('mageekguy\atoum\exceptions\runtime')
				->hasMessage('Source iterator injector must take one argument')
		;

		$directory = uniqid();

		$mockController = new mock\controller();
		$mockController
			->injectInNextMockInstance()
			->__construct = function() {}
		;

		$this->mock
			->generate('recursiveDirectoryIterator')
		;

		$iterator = new \mock\recursiveDirectoryIterator($directory);

		$this->assert
			->exception(function() use ($generator, $directory) { $generator->getSrcIterator($directory); })
				->isInstanceOf('unexpectedValueException')
				->hasMessage('RecursiveDirectoryIterator::__construct(' . $directory . '): failed to open dir: No such file or directory')
			->object($generator->setSrcIteratorInjector(function($directory) use ($iterator) { return $iterator; }))->isIdenticalTo($generator)
			->object($generator->getSrcIterator(uniqid()))->isIdenticalTo($iterator)
		;
	}

	public function testSetOutputWriter()
	{
		$generator = new phar\generator(uniqid());

		$stdout = new atoum\writers\std\out();

		$this->assert
			->object($generator->setOutputWriter($stdout))->isIdenticalTo($generator)
			->object($generator->getOutputWriter())->isIdenticalTo($stdout)
		;
	}

	public function testSetErrorWriter()
	{
		$generator = new phar\generator(uniqid());

		$stderr = new atoum\writers\std\err();

		$this->assert
			->object($generator->setErrorWriter($stderr))->isIdenticalTo($generator)
			->object($generator->getErrorWriter())->isIdenticalTo($stderr)
		;
	}

	public function testWriteMessage()
	{
		$generator = new phar\generator(uniqid());

		$this->mock
			->generate('mageekguy\atoum\writers\std\out')
		;

		$stdout = new \mock\mageekguy\atoum\writers\std\out();
		$stdout->getMockController()->write = function() {};

		$generator->setOutputWriter($stdout);

		$this->assert
			->object($generator->writeMessage($message = uniqid()))->isIdenticalTo($generator)
			->mock($stdout)
			->call('write', array($message . PHP_EOL))
		;
	}

	public function testWriteError()
	{
		$generator = new phar\generator(uniqid());

		$this->mock
			->generate('mageekguy\atoum\writers\std\err')
		;

		$stderr = new \mock\mageekguy\atoum\writers\std\err();
		$stderr->getMockController()->write = function() {};

		$generator->setErrorWriter($stderr);

		$this->assert
			->object($generator->writeError($error = uniqid()))->isIdenticalTo($generator)
			->mock($stderr)
			->call('write', array(sprintf($generator->getLocale()->_('Error: %s'), $error) . PHP_EOL))
		;
	}

	public function testRun()
	{
		$adapter = new atoum\test\adapter();

		$adapter->php_sapi_name = function() { return 'cli'; };
		$adapter->realpath = function($path) { return $path; };
		$adapter->is_dir = function() { return true; };
		$adapter->is_file = function() { return true; };
		$adapter->unlink = function() {};

		$generator = new phar\generator(uniqid(), null, $adapter);

		$this->assert
			->exception(function () use ($generator) {
					$generator->run();
				}
			)
				->isInstanceOf('mageekguy\atoum\exceptions\runtime')
				->hasMessage('Origin directory must be defined')
		;

		$generator->setOriginDirectory($originDirectory = uniqid());

		$this->assert
			->exception(function () use ($generator) {
					$generator->run();
				}
			)
				->isInstanceOf('mageekguy\atoum\exceptions\runtime')
				->hasMessage('Destination directory must be defined')
		;

		$generator->setDestinationDirectory(uniqid());

		$this->assert
			->exception(function () use ($generator) {
					$generator->run();
				}
			)
				->isInstanceOf('mageekguy\atoum\exceptions\runtime')
				->hasMessage('Stub file must be defined')
		;

		$generator->setStubFile($stubFile = uniqid());

		$adapter->is_readable = function() { return false; };

		$this->assert
			->exception(function () use ($generator) {
					$generator->run();
				}
			)
				->isInstanceOf('mageekguy\atoum\exceptions\runtime')
				->hasMessage('Origin directory \'' . $generator->getOriginDirectory() . '\' is not readable')
		;

		$adapter->is_readable = function($path) use ($originDirectory) { return ($path === $originDirectory); };

		$adapter->is_writable = function() { return false; };

		$this->assert
			->exception(function () use ($generator) {
					$generator->run();
				}
			)
				->isInstanceOf('mageekguy\atoum\exceptions\runtime')
				->hasMessage('Destination directory \'' . $generator->getDestinationDirectory() . '\' is not writable')
		;

		$adapter->is_writable = function() { return true; };

		$this->assert
			->exception(function () use ($generator) {
					$generator->run();
				}
			)
				->isInstanceOf('mageekguy\atoum\exceptions\runtime')
				->hasMessage('Stub file \'' . $generator->getStubFile() . '\' is not readable')
		;

		$adapter->is_readable = function($path) use ($originDirectory, $stubFile) { return ($path === $originDirectory || $path === $stubFile); };

		$generator->setPharInjector(function($name) { return null; });

		$this->assert
			->exception(function() use ($generator) {
					$generator->run();
				}
			)
				->isInstanceOf('mageekguy\atoum\exceptions\logic')
				->hasMessage('Phar injector must return a \phar instance')
		;

		$this->mock->generate('phar');

		$generator->setPharInjector(function($name) use (& $phar) {
				$pharController = new mock\controller();
				$pharController->__construct = function() {};
				$pharController->setStub = function() {};
				$pharController->setMetadata = function() {};
				$pharController->buildFromIterator = function() {};
				$pharController->setSignatureAlgorithm = function() {};
				$pharController->offsetGet = function() {};
				$pharController->injectInNextMockInstance();

				return ($phar = new \mock\phar($name));
			}
		);

		$generator->setSrcIteratorInjector(function($directory) { return null; });

		$this->assert
			->exception(function() use ($generator) {
					$generator->run();
				}
			)
				->isInstanceOf('mageekguy\atoum\exceptions\logic')
				->hasMessage('Source iterator injector must return a \recursiveDirectoryIterator instance')
		;

		$this->mock->generate('recursiveDirectoryIterator');

		$generator->setSrcIteratorInjector(function($directory) use (& $srcIterator) {
				$srcIteratorController = new mock\controller();
				$srcIteratorController->injectInNextMockInstance();
				$srcIteratorController->__construct = function() {};
				$srcIteratorController->injectInNextMockInstance();
				return ($srcIterator = new \mock\recursiveDirectoryIterator($directory));
			}
		);

		$generator->setConfigurationsIteratorInjector(function($directory) use (& $configurationsIterator) {
				$configurationsIteratorController = new mock\controller();
				$configurationsIteratorController->injectInNextMockInstance();
				$configurationsIteratorController->__construct = function() {};
				$configurationsIteratorController->injectInNextMockInstance();
				return ($configurationsIterator = new \mock\recursiveDirectoryIterator($directory));
			}
		);

		$adapter->file_get_contents = function($file) { return false; };

		$this->assert
			->exception(function() use ($generator) {
					$generator->run();
				}
			)
				->isInstanceOf('mageekguy\atoum\exceptions\runtime')
				->hasMessage('ABOUT file is missing in \'' . $generator->getOriginDirectory() . '\'')
		;

		$description = uniqid();

		$adapter->file_get_contents = function($file) use ($generator, $description) {
			switch ($file)
			{
				case $generator->getOriginDirectory() . DIRECTORY_SEPARATOR . 'ABOUT':
					return $description;

				default:
					return false;
			}
		};

		$this->assert
			->exception(function() use ($generator) {
					$generator->run();
				}
			)
				->isInstanceOf('mageekguy\atoum\exceptions\runtime')
				->hasMessage('COPYING file is missing in \'' . $generator->getOriginDirectory() . '\'')
		;

		$licence = uniqid();
		$stub = uniqid();

		$adapter->file_get_contents = function($file) use ($generator, $description, $licence, $stub) {
			switch ($file)
			{
				case $generator->getOriginDirectory() . DIRECTORY_SEPARATOR . 'ABOUT':
					return $description;

				case $generator->getOriginDirectory() . DIRECTORY_SEPARATOR . 'COPYING':
					return $licence;

				case $generator->getStubFile():
					return $stub;

				default:
					return uniqid();
			}
		};

		$this->assert
			->object($generator->run())->isIdenticalTo($generator)
			->mock($phar)
				->call('__construct', array(
						$generator->getDestinationDirectory() . DIRECTORY_SEPARATOR . atoum\scripts\phar\generator::phar, null, null, null
					)
				)
				->call('setMetadata', array(
						array(
							'version' => atoum\version,
							'author' => atoum\author,
							'support' => atoum\mail,
							'repository' => atoum\repository,
							'description' => $description,
							'licence' => $licence
						)
					)
				)
				->call('setStub', array($stub, null))
				->call('buildFromIterator', array(
						new \recursiveIteratorIterator(new atoum\src\iterator\filter($srcIterator)),
						$generator->getOriginDirectory()
					)
				)
				->call('setSignatureAlgorithm', array(
						\phar::SHA1,
						null
					)
				)
			->mock($srcIterator)
				->call('__construct', array($generator->getOriginDirectory(), null))
		;

		$superglobals = new atoum\superglobals();

		$superglobals->_SERVER = array('argv' => array(uniqid(), '--help'));

		$generator->setArgumentsParser(new atoum\script\arguments\parser($superglobals));

		$this->mock
			->generate('mageekguy\atoum\writers\std\out')
			->generate('mageekguy\atoum\writers\std\err')
		;

		$stdout = new \mock\mageekguy\atoum\writers\std\out();
		$stdout
			->getMockController()
			->write = function() {}
		;

		$stderr = new \mock\mageekguy\atoum\writers\std\err();
		$stderr
			->getMockController()
			->write = function() {}
		;

		$generator
			->setOutputWriter($stdout)
			->setErrorWriter($stderr)
		;

		$this->assert
			->object($generator->run())->isIdenticalTo($generator)
			->mock($stdout)
				->call('write', array(sprintf($generator->getLocale()->_('Usage: %s [options]'), $generator->getName()) . PHP_EOL))
				->call('write', array($generator->getLocale()->_('Available options are:') . PHP_EOL))
				->call('write', array('                    -h, --help: ' . $generator->getLocale()->_('Display this help') . PHP_EOL))
				->call('write', array('   -d <dir>, --directory <dir>: ' . $generator->getLocale()->_('Destination directory <dir>') . PHP_EOL))
		;

		$generator->setPharInjector(function($name) use (& $phar) {
				$pharController = new mock\controller();
				$pharController->injectInNextMockInstance();
				$pharController->__construct = function() {};
				$pharController->setStub = function() {};
				$pharController->setMetadata = function() {};
				$pharController->buildFromIterator = function() {};
				$pharController->setSignatureAlgorithm = function() {};
				$pharController->offsetGet = function() {};
				$pharController->injectInNextMockInstance();

				return ($phar = new \mock\phar($name));
			}
		);

		$this->assert
			->object($generator->run(array('-d', $directory = uniqid())))->isIdenticalTo($generator)
			->string($generator->getDestinationDirectory())->isEqualTo($directory)
			->mock($phar)
				->call('__construct', array(
						$generator->getDestinationDirectory() . DIRECTORY_SEPARATOR . atoum\scripts\phar\generator::phar, null, null, null
					)
				)
				->call('setMetadata', array(
					array(
						'version' => atoum\version,
						'author' => atoum\author,
						'support' => atoum\mail,
						'repository' => atoum\repository,
						'description' => $description,
						'licence' => $licence
						)
					)
				)
				->call('setStub', array($stub, null))
				->call('buildFromIterator', array(
						new \recursiveIteratorIterator(new atoum\src\iterator\filter($srcIterator)),
						$generator->getOriginDirectory()
					)
				)
				->call('setSignatureAlgorithm', array(
						\phar::SHA1,
						null
					)
				)
			->mock($srcIterator)
				->call('__construct', array($generator->getOriginDirectory(), null))
			->adapter($adapter)
				->call('unlink', array($directory . DIRECTORY_SEPARATOR . phar\generator::phar))
		;
	}
}

?>
