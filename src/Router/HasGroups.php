<?php

declare(strict_types = 1);

/**
 * Caldera Router
 * Routing component with controllers and route groups, part of Vecode Caldera
 * @author  biohzrdmx <github.com/biohzrdmx>
 * @copyright Copyright (c) 2022 Vecode. All rights reserved
 */

namespace Caldera\Router;

use Closure;

use Caldera\Router\Group;

trait HasGroups {

	/**
	 * Groups array
	 * @var array
	 */
	protected $groups = [];

	/**
	 * Create a nested route group
	 * @param  string  $prefix  Group prefix
	 * @param  Closure $closure Group definition callback
	 * @return Group
	 */
	public function group(string $prefix, Closure $closure): Group {
		$group = new Group();
		$group->setPrefix($prefix);
		call_user_func($closure, $group);
		$this->groups[] = $group;
		return $group;
	}

	/**
	 * Get the groups
	 * @return array
	 */
	public function getGroups(): array {
		return $this->groups;
	}
}
