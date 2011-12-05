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

namespace Vodacek\Form\Builder\Mappers;

use Vodacek\Form\Builder;

/**
 * @author Ondřej Vodáček <ondrej.vodacek@gmail.com>
 * @copyright 2011, Ondřej Vodáček
 * @license New BSD License
 */
class DefaultMapper implements IMapper {

	/**
	 * @param Builder\EntityForm $form
	 * @param Builder\Metadata $meta
	 * @return \Nette\Forms\Controls\BaseControl
	 */
	public function addFormControl(Builder\EntityForm $form, Builder\Metadata $meta) {
		$input = $form->addText($meta->name, $meta->label);
		$this->addConditions($input, $meta->conditions);
		return $input;
	}

	/**
	 * @param \Nette\Forms\FormControl $input
	 * @param array $conditions
	 */
	protected function addConditions(\Nette\Forms\FormControl $input, array $conditions) {
		foreach ($conditions as $key => $value) {
			switch ($key) {
				case 'required':
					$input->setRequired();
					break;
				case 'maxLength':
					$input->addRule(Builder\EntityForm::MAX_LENGTH, null, $value);
					break;
				case 'minLength':
					$input->addRule(Builder\EntityForm::MIN_LENGTH, null, $value);
					break;
				default:
					break;
			}
		}
		if (isset($conditions['min']) || isset($conditions['max'])) {
			$input->addRule(Builder\EntityForm::RANGE, null, array(
				isset($conditions['min']) ? $conditions['min'] : null,
				isset($conditions['max']) ? $conditions['max'] : null
			));
		}
	}

	/**
	 * @param mixed $value
	 * @return mixed
	 */
	public function formatForForm($value) {
		return $value;
	}

	/**
	 * @param \Nette\Forms\FormControl $control
	 * @return mixed
	 */
	public function formatForEntity(\Nette\Forms\FormControl $control) {
		return $control->getValue() ?: null;
	}
}
