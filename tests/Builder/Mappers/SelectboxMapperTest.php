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
 *     * Neither the name of the author nor the
 *       names of its contributors may be used to endorse or promote products
 *       derived from this software without specific prior written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS" AND
 * ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED
 * WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE
 * DISCLAIMED. IN NO EVENT SHALL COPYRIGHT HOLDER OR CONTRIBUTORS BE LIABLE FOR ANY
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
 * @author Ondřej Vodáček
 */
class SelectboxMapperTest extends \PHPUnit_Framework_TestCase {

	/** @var SelectboxMapper */
	private $object;

	/** @var \Nette\Forms\SelectBox */
	private $control;

	/** @var \Nette\Forms\Form */
	private $form;

	/** @var Builder\Metadata */
	private $metadata;

	public function setUp() {
		$this->control = $this->getMock('Nette\Forms\SelectBox');
		//~ $this->form = $this->getMock('Nette\Forms\Form');
		$this->form = $this->getMock('Vodacek\Form\Builder\EntityForm', [], [], '', false);
		$this->metadata = $meta = new Builder\Metadata();
		$meta->type = 'select';
		$meta->name = 'var';
		$meta->label = 'Select';
	}

	public function testAddFormControl() {
		$values = array('foo' => 'bar', 'baz' => 'qux');
		$this->object = $this->getMockBuilder('\Vodacek\Form\Builder\Mappers\SelectboxMapper')
				->setMethods(array('getValues'))
				->getMockForAbstractClass();
		$this->object->expects($this->any())
				->method('getValues')
				->will($this->returnValue($values));

		$this->form->expects($this->any())
				->method('addSelect')
				->with('var', 'Select', $values)
				->will($this->returnValue($this->control));

		$this->object->addFormControl($this->form, $this->metadata);
	}
}
