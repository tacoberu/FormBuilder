<?php
/*
 * Copyright (c) 2011, Ondřej Vodáček
 * All rights reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions are met:
 *     * Redistributions of source code must retain the above copyright
 *       notice, this list of conditions and the following disclaimer.
 *     * Redistributions in binary form must reproduce the above copyright
 *       notice, this list of conditions and the following disclaimer in the
 *       documentation and/or other materials provided with the distribution.
 *     * Neither the name of the Ondřej Vodáček nor the
 *       names of its contributors may be used to endorse or promote products
 *       derived from this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND
 * ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL Ondřej Vodáček BE LIABLE FOR ANY
 * DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES
 * (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES;
 * LOSS OF USE, DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND
 * ON ANY THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS
 * SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 */

namespace Vodacek\Form\Builder;

/**
 * @author Ondřej Vodáček <ondrej.vodacek@gmail.com>
 * @copyright 2011, Ondřej Vodáček
 * @license New BSD License
 */
class Builder {

	/** @var \Nette\ServiceLocator */
	private $mapperContainer;

	/** @var array */
	private $aliases = array(
		'text' => 'string',
		'integer' => 'number',
		'float' => 'number',
		'time' => 'date',
		'month' => 'date',
		'datetime' => 'date'
	);

	/** @var Loaders\ILoader */
	private $loader;

	/** @var \SplObjectStorage */
	private $entities;

	public function __construct(Loaders\ILoader $loader) {
		$this->entities = new \SplObjectStorage();
		$this->loader = $loader;
		$this->mapperContainer = new \Nette\ServiceLocator();
		$this->mapperContainer->addService('string', new Mappers\StringMapper());
		$this->mapperContainer->addService('number', new Mappers\NumberMapper());
		$this->mapperContainer->addService('date', new Mappers\DateMapper());
		$this->mapperContainer->addService('id', new Mappers\IdMapper());
		$this->mapperContainer->addService('boolean', new Mappers\BooleanMapper());
	}

	public function build($entity) {
		$form = new EntityForm($this);
		$metadata = $this->loader->load(is_object($entity) ? get_class($entity) : $entity);

		list($groups, $metadata) = self::splitByGroups($metadata);
		if (count($groups)) {
			foreach ($groups as $groupname => $group) {
				$form->addGroup($groupname);
				foreach ($group as $meta) {
					$this->getMapper($meta)->addFormControl($form, $meta);
				}
			}
			$form->setCurrentGroup();
		}

		foreach ($metadata as $meta) {
			$this->getMapper($meta)->addFormControl($form, $meta);
		}
		$this->entities[$form] = $entity;
		return $form;
	}

	/**
	 * @param EntityForm $form
	 */
	public function setDefaults(EntityForm $form) {
		$entity = $this->entities[$form];
		if (is_object($entity)) {
			$form->setDefaults($entity);
		}
	}

	/**
	 * @param object $values
	 * @return array
	 */
	public function formatForFrom(EntityForm $form, $values) {
		if (empty($values)) {
			return [];
		}
		$entity = $this->entities[$form];
		$metadata = $this->loader->load(is_object($entity) ? get_class($entity) : $entity);
		$formated = array();
		if (is_array($values) || $values instanceof \Traversable) {
			foreach ($values as $name => $value) {
				if (isset($metadata[$name])) {
					$meta = $metadata[$name];
					$value = $this->getMapper($meta)->toControlValue($value, $meta);
				}
				$formated[$name] = $value;
			}
		} else {
			foreach ($metadata as $meta) {
				if ($getter = $meta->getter) {
					$formated[$meta->name] = $this->getMapper($meta)->toControlValue($values->$getter(), $meta);
				}
			}
		}
		return $formated;
	}

	/**
	 * @param EntityForm $form
	 * @return object
	 */
	public function buildEntity(EntityForm $form) {
		$entity = $this->entities[$form];
		$class = null;
		if (is_object($entity)) {
			$class = get_class($entity);
		} else {
			$class = $entity;
			$entity = null;
		}
		$metadata = $this->loader->load($class);
		$values = array();
		foreach ($metadata as $name => $meta) {
			switch (true) {
				case $form[$name] instanceof \Nette\Forms\FormControl:
					$values[$name] = $this->getMapper($meta)->toPropertyValue($form[$name], $meta);
					break;
				case $form[$name] instanceof \Nette\Forms\FormContainer:
					$values[$name] = $this->getMapper($meta)->toPropertyValues($form[$name], $meta);
					break;
				default:
					throw new \LogicException("Unsupported type of `$name' control.");
			}
		}
		if ($form->hasErrors()) {
			return null;
		}

		$original = $form->getOriginal();
		if (!$entity) {
			$ref = new \ReflectionClass($class);
			$entity = null;
			if ($ref->hasMethod('__construct')) {
				$args = array();
				foreach ($ref->getMethod('__construct')->getParameters() as $param) {
					if (isset($values[$param->getName()])) {
						$args[] = $values[$param->getName()];
						unset($values[$param->getName()]);
					} elseif ($original) {
						// budeme předpokládat, že když už spoléháme na magii, takže tam je alespoň ten getter.
						$getter = 'get' . ucfirst($param->getName());
						if (method_exists($original, $getter)) {
							$args[] = $original->$getter();
						} else {
							throw new \LogicException("Cannot make instance of '{$class}'. Missing getter whitch name must correlate with name of param of constructor: '{$param->getName()}'.");
						}
					} else {
						throw new \LogicException("Cannot make instance of '{$class}'. Missing data for param of constructor: '{$param->getName()}'.");
					}
				}
				$entity = $ref->newInstanceArgs($args);
			} else {
				$entity = $ref->newInstance();
			}
		}

		// Překopírovat data ze setterů originálu.
		if ($original) {
			$ref = new \ReflectionClass($original);
			foreach ($ref->getMethods(\ReflectionMethod::IS_PUBLIC) as $method) {
				if (strncmp($method->getName(), 'get', 3) === 0) {
					$getter = $method->getName();
					$setter = 'set' . ucfirst(substr($method->getName(), 3));
					if (method_exists($original, $setter)) {
						$entity->$setter($original->$getter());
					}
				}
			}
		}

		// Nastavit hodnoty z formuláře.
		foreach ($values as $name => $value) {
			if ($setter = $metadata[$name]->setter) {
				$entity->$setter($value);
			}
		}

		return $entity;
	}

	/**
	 * @param Metadata $meta
	 * @return Mappers\IMapper
	 */
	private function getMapper(Metadata $meta) {
		$type = isset($this->aliases[$meta->type]) ? $this->aliases[$meta->type] : $meta->type;
		return $this->mapperContainer->getService($type);
	}

	/**
	 * @param string $name
	 * @param string $target
	 */
	public function addAlias($name, $target) {
		$this->aliases[$name] = $target;
	}

	/**
	 * @param string $name
	 * @param IMapper $mapper
	 */
	public function addMapper($name, $mapper) {
		$this->mapperContainer->addService($name, $mapper);
	}

	private static function splitByGroups(array $metadata) {
		$groups = [];
		$withoutgroups = [];
		foreach ($metadata as $key => $item) {
			if (isset($item->custom['group'])) {
				$name = $item->custom['group'];
				if ( ! isset($groups[$name])) {
					$groups[$name] = [];
				}

				$groups[$name][$key] = $item;
			}
			else {
				$withoutgroups[$key] = $item;
			}
		}
		return [$groups, $withoutgroups];
	}

}
