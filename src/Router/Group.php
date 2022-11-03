<?php

declare(strict_types = 1);

/**
 * Caldera Router
 * Routing component with controllers and route groups, part of Vecode Caldera
 * @author  biohzrdmx <github.com/biohzrdmx>
 * @copyright Copyright (c) 2022 Vecode. All rights reserved
 */

namespace Caldera\Router;

use Caldera\Router\HasGroups;
use Caldera\Router\HasRoutes;

class Group {

	use HasGroups,
		HasRoutes;

	/**
	 * Group prefix
	 * @var string
	 */
	protected $prefix = '';

	/**
	 * Name
	 * @var string
	 */
	protected $name = '';

	/**
	 * Set the group prefix
	 * @param  string $prefix Group prefix
	 * @return $this
	 */
	public function setPrefix(string $prefix) {
		$this->prefix = $prefix;
		if ($this->name == '') {
			$this->name = trim(str_replace('/', '-', $prefix), '-');
		}
		return $this;
	}

	/**
	 * Set name
	 * @param  string $name Route name
	 * @return $this
	 */
	public function setName(string $name) {
		$this->name = $name;
		return $this;
	}

	/**
	 * Get group prefix
	 * @return string
	 */
	public function getPrefix(): string {
		return $this->prefix;
	}

	/**
	 * Get name
	 * @return string
	 */
	public function getName(): string {
		return $this->name;
	}
}
