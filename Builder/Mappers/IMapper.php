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
 * Interface for Mapper objects.
 *
 * @author Ondřej Vodáček <ondrej.vodacek@gmail.com>
 * @copyright 2011, Ondřej Vodáček
 * @license New BSD License
 */
interface IMapper {

	/**
	 * Add a control to the form and setup it according to supplied metadata.
	 *
	 * @param \Nette\Forms\Form $form
	 * @param Builder\Metadata $metadata
	 * @return \Nette\Forms\Controls\BaseControl
	 */
	public function addFormControl(\Nette\Forms\Form $form, Builder\Metadata $metadata);

	/**
	 * Format the value from entity to be used in control.
	 *
	 * @param mixed $value
	 * @param Builder\Metadata $metadata
	 * @return mixed
	 */
	public function toControlValue($value, Builder\Metadata $metadata);

	/**
	 * Format the value from control to be set to the entity.
	 *
	 * @param \Nette\Forms\Controls\BaseControl $control
	 * @param Builder\Metadata $metadata
	 * @return mixed
	 */
	public function toPropertyValue(\Nette\Forms\Controls\BaseControl $control, Builder\Metadata $metadata);
}
