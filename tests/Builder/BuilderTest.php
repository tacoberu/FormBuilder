<?php
/*
 * Copyright (c) 2016, Martin Takáč
 */

namespace Vodacek\Form\Builder;

require_once __dir__ . '/../Utils/LimitedScope.php';

use PHPUnit_Framework_TestCase;
use Vodacek\Form\Builder\Loaders;
use Gajus\Dindent;
use ReflectionClass;


/**
 * @author Martin Takáč <martin@takac.name>
 */
class BuilderTest extends PHPUnit_Framework_TestCase {

	protected function setUp() {
		$loader = new Loaders\AnnotationLoader();
		$this->builder = new Builder($loader);
	}

	public function _testLoad() {
		/*
		$metadata = $this->object->load('AnnotationLoaderTest_TestEntity');
		$expected = \Nette\Utils\LimitedScope::load(self::getFixtuteDir() . '/TestEntityMetadata.php');
		$this->assertEquals($expected, $metadata);
		*/

		$loader = $this->getMock(Loaders\ILoader::class, [], [], '', false);
		$builder = new Builder($loader);

		dump($builder->build(BuilderTestPojo1::class));
	}


	public function testLoadPojoWithoutContent() {
		$form = $this->builder->build(BuilderTestPojo1::class);
		$this->assertInstanceOf(EntityForm::class, $form);
		$this->assertStringEqualsFileFixtures(__FUNCTION__, self::beautifierHtml(self::removeToken($form)));
	}

	public function testLoadPojoWithoutConstructor() {
		$form = $this->builder->build(BuilderTestPojo2::class);
		$this->assertStringEqualsFileFixtures(__FUNCTION__, self::beautifierHtml(self::removeToken($form)));
	}

	public function testBuildPojoWithConstructorAndWithoutSetter() {
		$form = $this->builder->build(BuilderTestPojo3::class);
		$this->assertStringEqualsFileFixtures(__FUNCTION__, self::beautifierHtml(self::removeToken($form)));
	}

	public function testBuildValueWithConstructorAndWithoutSetter() {
		$entry = new BuilderTestPojo3('Lojza');
		$form = $this->builder->build($entry);
		$this->assertStringEqualsFileFixtures(__FUNCTION__, self::beautifierHtml(self::removeToken($form)));
	}

	public function testBuildValueSetWithConstructorAndWithoutSetter() {
		$entry = new BuilderTestPojo3('Lojza');
		$form = $this->builder->build($entry);
		$form->setDefaults($entry);
		$this->assertStringEqualsFileFixtures(__FUNCTION__, self::beautifierHtml(self::removeToken($form)));
	}

	public function testBuildFromEmptyDefinition() {
		$loader = new Loaders\DefinitionLoader();
		$this->builder = new Builder($loader);
		$form = $this->builder->build([]);
		$this->assertStringEqualsFileFixtures(__FUNCTION__, self::beautifierHtml(self::removeToken($form)));
	}

	public function testBuildSimpleDefinition() {
		$loader = new Loaders\DefinitionLoader();
		$this->builder = new Builder($loader);
		$form = $this->builder->build([
			['name' => 'name', 'type' => 'string', 'label' => 'Jméno'],
		]);
		$this->assertStringEqualsFileFixtures(__FUNCTION__, self::beautifierHtml(self::removeToken($form)));
	}

	public function testBuildGroupedDefinition() {
		$loader = new Loaders\DefinitionLoader();
		$this->builder = new Builder($loader);
		$form = $this->builder->build([
			['name' => 'name', 'type' => 'string', 'label' => 'Jméno', 'group' => 'Tender specification'],
		]);
		$this->assertStringEqualsFileFixtures(__FUNCTION__, self::beautifierHtml(self::removeToken($form)));
	}

	private function assertStringEqualsFileFixtures($key, $actual, $message = null) {
		$this->assertStringEqualsFile($this->getFixtureFile($key, 'html'), trim($actual) . PHP_EOL, $message);
	}

	/**
	 * @return string
	 */
	private function getFixtureFile($testName, $ext = '') {
		return $this->getFixtureDir() . '/' . lcfirst(substr($testName, 4)) . '.' . $ext;
	}

	/**
	 * @return string
	 */
	private function getFixtureDir() {
		$ref = new ReflectionClass($this);
		return dirname($ref->getFileName()) . '/fixtures/' . $ref->getShortName();
	}

	/**
	 * @param string
	 * @return string
	 */
	private static function removeToken($s) {
		return preg_replace('~id="frm-_token_" value="[^"]+">~', 'id="frm-_token_" value="***">', $s);
	}

	/**
	 * @param string
	 * @return string
	 */
	private static function beautifierHtml($s) {
		$indenter = new Dindent\Indenter();
		return $indenter->indent($s);
	}

}



/**
 * Without any content.
 */
class BuilderTestPojo1 {}



/**
 * Without constructor.
 */
class BuilderTestPojo2 {

	/**
	 * @Input(label="Description", group="Tender specification", type=text)
	 * @var string
	 */
	private $name;

	function setName($val)
	{
		$this->name = $val;
	}

	function getName()
	{
		return $this->name;
	}
}



/**
 * With constructor and without setter.
 */
class BuilderTestPojo3 {

	/**
	 * @Input(label="Description", group="Tender specification", type=text)
	 * @var string
	 */
	private $name;

	function __construct($val)
	{
		$this->name = $val;
	}

	function getName()
	{
		return $this->name;
	}
}
