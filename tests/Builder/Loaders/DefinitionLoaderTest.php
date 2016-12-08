<?php
/*
 * Copyright (c) 2016, Martin Takáč
 */

namespace Vodacek\Form\Builder\Loaders;

use PHPUnit_Framework_TestCase;
use Vodacek\Form\Builder;


/**
 * @author Martin Takáč <martin@takac.name>
 */
class DefinitionLoaderTest extends PHPUnit_Framework_TestCase {

	/** @var DefinitionLoader */
	protected $loader;

	protected function setUp() {
		$this->loader = new DefinitionLoader();
	}

	public function testEmpty() {
		$this->assertEquals([], $this->loader->load([]));
	}

	public function testSimple() {
		$this->assertEquals([
			'nomen' => self::createMetadata('nomen', 'string', 'Jméno'),
		], $this->loader->load([
			['name' => 'nomen', 'type' => 'string', 'label' => 'Jméno'],
		]));
	}

	public function testWithGrouped() {
		$this->assertEquals([
			'nomen' => self::createMetadata('nomen', 'string', 'Jméno', ['group' => 'G']),
		], $this->loader->load([
			['name' => 'nomen', 'type' => 'string', 'label' => 'Jméno', 'group' => 'G'],
		]));
	}

	/**
	 * Protože tady někdo neví, k čemu je konstruktor.
	 */
	private static function createMetadata($name, $type, $label, $custom = [])
	{
		$entry = new Builder\Metadata();
		$entry->name = $name;
		$entry->type = $type;
		$entry->label = $label;
		$entry->custom = $custom;

		return $entry;
	}
}
