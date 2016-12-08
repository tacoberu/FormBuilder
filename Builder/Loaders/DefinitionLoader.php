<?php
/*
 * Copyright (c) 2016, Martin Takáč
 */

namespace Vodacek\Form\Builder\Loaders;

use Vodacek\Form\Builder;

/**
 * Loader using defintion.
 *
 * @author Martin Takáč <martin@takac.name>
 */
class DefinitionLoader implements ILoader {

	/**
	 * @param array $defintion
	 * @return array array<Builder\Metadata>
	 */
	public function load($defintions) {
		if (empty($defintions)) {
			return [];
		}
		$ret = [];
		foreach ($defintions as $def) {
			$m = $this->buildMetadata($def);
			if ($m) {
				$ret[$m->name] = $m;
			}
		}

		return $ret;
	}

	/**
	 * @param array
	 * @return Metadata|null
	 */
	private function buildMetadata(array $def) {
		$custom = [];
		$entry = new Builder\Metadata();
		$entry->name = $def['name'];
		//~ $entry->getter = 'get'.ucfirst($meta->name);
		//~ $entry->setter = 'set'.ucfirst($meta->name);
		foreach ($def as $key => $val) {
			switch ($key) {
				case 'name':
					break;
				case 'label':
				case 'type':
					$entry->$key = $val;
					break;
				case 'required':
				case 'minLength':
				case 'maxLength':
				case 'min':
				case 'max':
					$entry->conditions[$key] = $val;
					break;
				default:
					$custom[$key] = $val;
			}
			$entry->custom = $custom;
		}

		return $entry;
	}

}
