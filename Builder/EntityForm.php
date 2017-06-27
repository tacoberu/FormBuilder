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
 * Extended Nette Form with automated entity - form values conversion.
 *
 * @author Ondřej Vodáček <ondrej.vodacek@gmail.com>
 * @copyright 2011, Ondřej Vodáček
 * @license New BSD License
 */
class EntityForm extends \Nette\Application\AppForm {

	/** @var Builder */
	private $builder;

	/** @var object */
	private $orig = null;

	/**
	 * @param Builder $builder
	 */
	public function __construct(Builder $builder) {
		parent::__construct();
		$this->builder = $builder;
	}

	/**
	 * Fill-in with default values.
	 *
	 * @param  array|Traversable|object $values
	 * @param  bool $erase erase other default values?
	 * @return EntityForm provides a fluent interface
	 */
	public function setDefaults($values, $erase = FALSE) {
		$this->orig = $values;
		$values = $this->builder->formatForFrom($this, $values);
		return parent::setDefaults($values, $erase);
	}

	/**
	 * @return object
	 */
	public function getOriginal() {
		return $this->orig;
	}

	/**
	 * This method will be called when the component (or component's parent)
	 * becomes attached to a monitored object. Do not call this method yourself.
	 * @param  Nette\ComponentModel\IComponent
	 * @return void
	 */
	protected function attached($presenter) {
		// fill-in the form with HTTP data, preserve read-only value
		if ($this->isSubmitted()) {
			$preserved = [];
			foreach ($this->getControls() as $control) {
				if ($control->isDisabled()) {
					$preserved[$control->name] = $control->value;
				}
			}
		}

		parent::attached($presenter);

		// fill-in preserved read-only value.
		if (isset($preserved)) {
			foreach ($preserved as $name => $value) {
				$this[$name]->value = $value;
			}
		}

		if ($presenter instanceof \Nette\Application\Presenter) {
			$this->builder->setDefaults($this);
		}
	}

	/**
	 * Call to undefined method.
	 * @param  string  method name
	 * @param  array   arguments
	 * @return mixed
	 * @throws MemberAccessException
	 */
	public function __call($name, $args) {
		if ($name === 'onSubmit') {
			// Pokud na nějakém prvku máme vypnutou validaci, tak nejsem schopen zajistit, že mapování na objekt dopadne dobře.
			// Mapování vypínám ve dvou případech: tlačítko cancel, a replikator
			if ($this->submitted instanceof \Nette\Forms\SubmitButton && ! $this->submitted->getValidationScope()) {
				$entity = $this->getOriginal();
			} else {
				$entity = $this->builder->buildEntity($args[0]);
			}
			if (!$entity) {
				return;
			}
			$args = array($entity, $args[0]);
		}
		return parent::__call($name, $args);
	}
}
