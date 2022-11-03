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

trait HasRoutes {

	/**
	 * Routes array
	 * @var array
	 */
	protected $routes = [];

	/**
	 * Get the routes
	 * @return array
	 */
	public function getRoutes(): array {
		return $this->routes;
	}

	/**
	 * Add route for GET and HEAD methods
	 * @param  string $slug    Route slug
	 * @param  mixed  $handler Route handler
	 * @param  bool   $insert  Whether to insert the route or not
	 * @return Route
	 */
	public function get(string $slug, $handler, bool $insert = false): Route {
		return $this->add($slug, $handler, ['GET', 'HEAD'], $insert);
	}

	/**
	 * Add route for POST method
	 * @param  string $slug    Route slug
	 * @param  mixed  $handler Route handler
	 * @param  bool   $insert  Whether to insert the route or not
	 * @return Route
	 */
	public function post(string $slug, $handler, bool $insert = false): Route {
		return $this->add($slug, $handler, ['POST'], $insert);
	}

	/**
	 * Add route for PUT method
	 * @param  string $slug    Route slug
	 * @param  mixed  $handler Route handler
	 * @param  bool   $insert  Whether to insert the route or not
	 * @return Route
	 */
	public function put(string $slug, $handler, bool $insert = false): Route {
		return $this->add($slug, $handler, ['PUT'], $insert);
	}

	/**
	 * Add route for PATCH method
	 * @param  string $slug    Route slug
	 * @param  mixed  $handler Route handler
	 * @param  bool   $insert  Whether to insert the route or not
	 * @return Route
	 */
	public function patch(string $slug, $handler, bool $insert = false): Route {
		return $this->add($slug, $handler, ['PATCH'], $insert);
	}

	/**
	 * Add route for OPTIONS method
	 * @param  string $slug    Route slug
	 * @param  mixed  $handler Route handler
	 * @param  bool   $insert  Whether to insert the route or not
	 * @return Route
	 */
	public function options(string $slug, $handler, bool $insert = false): Route {
		return $this->add($slug, $handler, ['OPTIONS'], $insert);
	}

	/**
	 * Add route for DELETE method
	 * @param  string $slug    Route slug
	 * @param  mixed  $handler Route handler
	 * @param  bool   $insert  Whether to insert the route or not
	 * @return Route
	 */
	public function delete(string $slug, $handler, bool $insert = false): Route {
		return $this->add($slug, $handler, ['DELETE'], $insert);
	}

	/**
	 * Add route for HEAD method
	 * @param  string $slug    Route slug
	 * @param  mixed  $handler Route handler
	 * @param  bool   $insert  Whether to insert the route or not
	 * @return Route
	 */
	public function head(string $slug, $handler, bool $insert = false): Route {
		return $this->add($slug, $handler, ['HEAD'], $insert);
	}

	/**
	 * Add route for any method
	 * @param  string $slug    Route slug
	 * @param  mixed  $handler Route handler
	 * @param  bool   $insert  Whether to insert the route or not
	 * @return Route
	 */
	public function any(string $slug, $handler, bool $insert = false): Route {
		return $this->add($slug, $handler, ['*'], $insert);
	}

	/**
	 * Add route for specific methods
	 * @param  array  $methods Array of methods
	 * @param  string $slug    Route slug
	 * @param  mixed  $handler Route handler
	 * @param  bool   $insert  Whether to insert the route or not
	 * @return Route
	 */
	public function match(array $methods, string $slug, $handler, bool $insert = false): Route {
		return $this->add($slug, $handler, $methods, $insert);
	}

	/**
	 * Add a new route
	 * @param  string $slug    Route slug
	 * @param  mixed  $handler Route handler
	 * @param  array  $methods HTTP method
	 * @param  bool   $insert  Whether to insert the route or add it at the end
	 * @return Route
	 */
	protected function add(string $slug, $handler, array $methods, bool $insert = false): Route {
		$route = new Route();
		$route->setSlug($slug)
			->setHandler($handler)
			->setMethods($methods);
		if ($insert) {
			array_unshift($this->routes, $route);
		} else {
			array_push($this->routes, $route);
		}
		return $route;
	}
}
