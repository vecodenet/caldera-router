<?php

declare(strict_types = 1);

/**
 * Caldera Router
 * Routing component with controllers and route groups, part of Vecode Caldera
 * @author  biohzrdmx <github.com/biohzrdmx>
 * @copyright Copyright (c) 2022 Vecode. All rights reserved
 */

namespace Caldera\Router;

use BadMethodCallException ;

abstract class Controller {

	/**
	 * Handle calls to missing methods
	 * @param  string $method     Method name
	 * @param  array  $parameters Parameters
	 * @return mixed
	 */
	public function __call(string $method, array $parameters) {
		throw new BadMethodCallException( sprintf("Method '%s::%s' does not exist", static::class, $method) );
	}
}