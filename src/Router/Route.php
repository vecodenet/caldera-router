<?php

declare(strict_types = 1);

/**
 * Caldera Router
 * Routing component with controllers and route groups, part of Vecode Caldera
 * @author  biohzrdmx <github.com/biohzrdmx>
 * @copyright Copyright (c) 2022 Vecode. All rights reserved
 */

namespace Caldera\Router;

class Route {

	/**
	 * Route name
	 * @var string
	 */
	protected $name;

	/**
	 * Route slug
	 * @var string
	 */
	protected $slug;

	/**
	 * Route handler
	 * @var mixed
	 */
	protected $handler;

	/**
	 * Route methods
	 * @var array
	 */
	protected $methods;

	/**
	 * Route constraints
	 * @var array
	 */
	protected $constraints;

	/**
	 * Check if the route has the specified method
	 * @param  string  $method HTTP method
	 * @return bool
	 */
	public function hasMethod(string $method): bool {
		return in_array('*', $this->methods) || in_array(strtoupper($method), $this->methods);
	}

	/**
	 * Get route name
	 * @return string
	 */
	public function getName(): string {
		return $this->name;
	}

	/**
	 * Get route slug
	 * @return string
	 */
	public function getSlug(): string {
		return $this->slug;
	}

	/**
	 * Get route handler
	 * @return mixed
	 */
	public function getHandler() {
		return $this->handler;
	}

	/**
	 * Get route methods
	 * @return array
	 */
	public function getMethods(): array {
		return $this->methods;
	}

	/**
	 * Get route constraint
	 * @param  string $parameter Parameter name
	 * @return string
	 */
	public function getConstraint(string $parameter): string {
		return $this->constraints[$parameter] ?? '';
	}

	/**
	 * Set route name
	 * @param  string $name Route name
	 * @return $this
	 */
	public function setName(string $name) {
		$this->name = $name;
		return $this;
	}

	/**
	 * Set route slug
	 * @param  string $slug Route slug
	 * @return $this
	 */
	public function setSlug(string $slug) {
		$this->slug = $slug;
		return $this;
	}

	/**
	 * Set route handler
	 * @param  mixed $handler Route handler
	 * @return $this
	 */
	public function setHandler($handler) {
		$this->handler = $handler;
		return $this;
	}

	/**
	 * Set route methods
	 * @param  array $methods Route methods
	 * @return $this
	 */
	public function setMethods(array $methods) {
		$this->methods = array_map('strtoupper', $methods);
		return $this;
	}

	/**
	 * Set route constraint
	 * @param  string $parameter  Parameter name
	 * @param  string $constraint Constraint expression
	 * @return $this
	 */
	public function setConstraint(string $parameter, string $constraint) {
		$this->constraints[$parameter] = $constraint;
		return $this;
	}
}
