<?php

declare(strict_types = 1);

/**
 * Caldera Router
 * Routing component with controllers and route groups, part of Vecode Caldera
 * @author  biohzrdmx <github.com/biohzrdmx>
 * @copyright Copyright (c) 2022 Vecode. All rights reserved
 */

namespace Caldera\Router;

use Caldera\Router\Route;

class CompiledRoute {

	/**
	 * Route object
	 * @var Route
	 */
	protected $route;

	/**
	 * Parameters array
	 * @var array
	 */
	protected $parameters;

	/**
	 * Route pattern
	 * @var string
	 */
	protected $pattern;

	/**
	 * Set route
	 * @param  Route $route Route object
	 * @return $this
	 */
	public function setRoute(Route $route) {
		$this->route = $route;
		return $this;
	}

	/**
	 * Set route pattern
	 * @param  string $pattern Route pattern
	 * @return $this
	 */
	public function setPattern(string $pattern) {
		$this->pattern = $pattern;
		return $this;
	}

	/**
	 * Set parameters
	 * @param  array $parameters Parameters array
	 * @return $this
	 */
	public function setParameters(array $parameters) {
		$this->parameters = $parameters;
		return $this;
	}

	/**
	 * Get route name
	 * @return string
	 */
	public function getName(): string {
		return $this->route !== null ? $this->route->getName() : '';
	}

	/**
	 * Get route
	 * @return Route
	 */
	public function getRoute(): Route {
		return $this->route;
	}

	/**
	 * Get route pattern
	 * @return string
	 */
	public function getPattern(): string {
		return $this->pattern;
	}

	/**
	 * Get parameters
	 * @return array
	 */
	public function getParameters(): array {
		return $this->parameters;
	}

	/**
	 * Get parameter
	 * @param  string $parameter Parameter name
	 * @param  string $default   Default value
	 * @return string
	 */
	public function getParameter(string $parameter, string $default = ''): string {
		return $this->parameters[$parameter] ?? $default;
	}
}
