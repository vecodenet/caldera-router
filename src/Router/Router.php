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
use Exception;
use RuntimeException;
use ReflectionClass;
use ReflectionFunction;
use ReflectionMethod;

use Psr\Container\ContainerInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;

use Caldera\Router\Group;
use Caldera\Router\HasGroups;
use Caldera\Router\HasRoutes;
use Caldera\Router\NotFoundException;

class Router {

	use HasGroups,
		HasRoutes;

	/**
	 * Collected flag
	 * @var bool
	 */
	protected $collected;

	/**
	 * Compiled routes array
	 * @var array
	 */
	protected $compiled = [];

	/**
	 * ContainerInterface implementation
	 * @var ContainerInterface
	 */
	protected $container;

	/**
	 * ResponseFactoryInterface implementation
	 * @var ResponseFactoryInterface
	 */
	protected $factory;

	/**
	 * Default route
	 * @var string
	 */
	protected $default = '/index';

	/**
	 * Directory path
	 * @var string
	 */
	protected $directory = '';

	/**
	 * Constructor
	 * @param ContainerInterface       $container ContainerInterface implementation
	 * @param ResponseFactoryInterface $factory   ResponseFactoryInterface implementation
	 */
	function __construct(ContainerInterface $container, ResponseFactoryInterface $factory) {
		$this->container = $container;
		$this->factory = $factory;
	}

	/**
	 * Set default route
	 * @param  string $default Default route
	 * @return $this
	 */
	public function setDefault(string $default) {
		$this->default = $default;
		return $this;
	}

	/**
	 * Set directory path
	 * @param  string $directory Directory path
	 * @return $this
	 */
	public function setDirectory(string $directory) {
		$this->directory = trim($directory, '/');
		return $this;
	}

	/**
	 * Get default route
	 * @return string $default Default route
	 */
	public function getDefault(): string {
		return $this->default;
	}

	/**
	 * Get directory path
	 * @return string $directory Directory path
	 */
	public function getDirectory(): string {
		return $this->directory;
	}

	/**
	 * Dispatch the request
	 * @param  ServerRequestInterface   $request Request object
	 * @return ResponseInterface
	 */
	public function dispatch(ServerRequestInterface $request): ResponseInterface {
		# Parse the request URI and get the requested resource
		$directory = trim($this->directory, '/');
		$uri = $request->getUri();
		$path = $uri->getPath();
		$resource = '';
		$path = ltrim($path, '/');
		$path = preg_replace("#^{$directory}#", '', $path);
		$path = trim($path, '/');
		$path = $path ? $path : ltrim($this->default, '/');
		$resource = "/{$path}";
		# Collect routes
		$collected = $this->collect();
		# Get HTTP method
		$method = strtoupper( $request->getMethod() );
		# Match resource against route patterns
		$compiled = null;
		$params = [];
		foreach ($collected as $entry) {
			$route = $entry->getRoute();
			$matches_method = $route->hasMethod($method);
			$matches_pattern = preg_match($entry->getPattern(), $resource, $matches) === 1;
			if ( $matches_method && $matches_pattern ) {
				$matches = array_splice($matches, 1);
				foreach ($entry->getParameters() as $key => $value) {
					$params[$key] = array_shift($matches);
				}
				$compiled = $entry;
				break;
			}
		}
		if ($compiled) {
			$route = $compiled->getRoute();
			$handler = $route->getHandler();
			$callable = null;
			$arguments = [];
			if ( $handler instanceof Closure ) {
				# Closure
				$callable = $handler;
				# Get reflector
				$reflector = new ReflectionFunction($callable);
				$arguments = $reflector->getParameters();
			} else if ( is_array($handler) ) {
				# Try to get class and method name
				$class = $handler[0] ?? '';
				$method = $handler[1] ?? '';
				if ( $class && $method ) {
					# Try to get from container
					$instance = $this->container->get($class);
					$callable = [$instance, $method];
					# Get reflector
					$reflector = new ReflectionMethod($instance, $method);
					$arguments = $reflector->getParameters();
				} else {
					# Handler can not be resolved
					throw new RuntimeException('Invalid handler specified');
				}
			}
			if ( is_callable($callable) ) {
				# Create a default response
				$response = $this->factory->createResponse(200);
				# Call route handler
				$callable_args = [];
				if ($arguments) {
					foreach ($arguments as $argument) {
						switch ( $argument->getName() ) {
							case 'request':
								# Use the Request instance
								$callable_args[] = $request;
							break;
							case 'response':
								# Use the Response instance
								$callable_args[] = $response;
							break;
							default:
								# Check if the parameter is on the URI
								$val = $params[ $argument->name ] ?? null;
								if ( $val !== null ) {
									# It is present, use it
									$callable_args[] = $val;
								} else {
									# Not in the URI, try with a default value maybe?
									if ( $argument->isDefaultValueAvailable() ) {
										# Default value available, use it
										$callable_args[] = $argument->getDefaultValue();
									} else {
										# No default value, try to resolve it from the container
										$type = $argument->getType();
										try {
											# This may raise an exception
											$dependency = new ReflectionClass( $type->getName() ); # @phpstan-ignore-line
											$callable_args[] = $this->container->get( $dependency->name );
										} catch (Exception $e) {
											throw new RuntimeException("Can not resolve parameter '{$argument->name}'");
										}
									}
								}
							break;
						}
					}
				}
				# Call route handler
				$ret = call_user_func_array($callable, $callable_args);
				if ($ret instanceof ResponseInterface) {
					$response = $ret;
				}
				return $response;
			}
		}
		# If we got to here then the request hasn't been handled
		throw new NotFoundException();
	}

	/**
	 * Get the URL to a named route
	 * @param  string $name       Route name
	 * @param  array  $parameters Route parameters
	 * @return string
	 */
	public function route(string $name, array $parameters = []): string {
		$ret = '';
		$collected = $this->collect();
		if ($collected) {
			$compiled = null;
			# Get the route
			foreach ($collected as $entry) {
				if ( $name == $entry->getName() ) {
					$compiled = $entry;
					break;
				}
			}
			if ($compiled) {
				$route = $compiled->getRoute();
				# Replace named parameters
				$path = preg_replace_callback('/\{(.*?)(\?)?\}/', function($match) use ($compiled, &$parameters) {
					$name = $match[1];
					$optional = ($match[2] ?? '') == '?';
					# Get parameter constraint
					$parameter = $parameters[$name] ?? '';
					$constraint = $compiled->getParameter($name, '([^\/]+)');
					$pattern = "/{$constraint}/";
					# Check parameter against the constraint
					if (!$optional && preg_match($pattern, (string) $parameter) !== 1 ) {
						throw new RuntimeException("Required parameter '{$name}' does not match condition");
					}
					if ($parameter !== null) {
						unset( $parameters[$name] );
					}
					return $parameter;
				}, $route->getSlug());
				# Convert extra parameters into a query string
				if ($parameters) {
					$path .= '?' . http_build_query($parameters);
				}
				# Remove consecutive slashes resulting from optional parameters
				$ret = preg_replace('~/+~', '/', $path);
			}
		}
		if (! $ret ) {
			throw new RuntimeException("Unknown route '{$name}'");
		}
		return is_string($ret) ? $ret : '';
	}

	/**
	 * Collect routes
	 * @param  bool $force Force recollection
	 * @return array
	 */
	public function collect(bool $force = false) {
		if ( $force || !$this->collected ) {
			$routes = $this->routes;
			if ( $this->groups ) {
				foreach ($this->groups as $group) {
					$routes = array_merge($routes, $this->flatten($group));
				}
			}
			$this->compiled = $this->compile($routes);
			$this->collected = true;
		}
		return $this->compiled;
	}

	/**
	 * Compile an array of routes
	 * @return array
	 */
	protected function compile(array $routes): array {
		$compiled = [];
		foreach ($routes as $route) {
			$pattern = $route->getSlug();
			# Replace parameters
			$parameters = [];
			$pattern = preg_replace_callback('/\/\{(.*?)(\?)?\}/', function($match) use ($route, &$parameters) {
				$constraint = $route->getConstraint( $match[1] );
				$optional = ($match[2] ?? '') == '?';
				# Format constraint
				$ret = $constraint ? "({$constraint})" : '([^\/]+)';
				$parameters[ $match[1] ] = $ret;
				# Optional parameters should have its trailing slash as optional too
				$ret = $optional ? "(?:/{$ret})?" : "/{$ret}";
				return $ret;
			}, $pattern);
			# Remove trailing slashes
			$pattern = rtrim($pattern, '/');
			# Format pattern
			$pattern = "~^{$pattern}$~";
			# And save route
			$compiled_route = new CompiledRoute();
			$compiled_route->setRoute($route)
				->setParameters($parameters)
				->setPattern($pattern);
			$compiled[] = $compiled_route;
		}
		return $compiled;
	}

	/**
	 * Flatten route groups
	 * @param  Group  $group     Route group to flatten
	 * @param  string $prefix    Current prefix
	 * @param  string $namespace Current namespace
	 * @return array
	 */
	protected function flatten(Group $group, string $prefix = '', string $namespace = ''): array {
		$flattened = [];
		# Create a base prefix
		$prefix = $this->join([ $prefix, $group->getPrefix() ]);
		$namespace = $this->join([ $namespace, $group->getName() ]);
		$routes = $group->getRoutes();
		if ($routes) {
			foreach ($routes as $route) {
				$slug = $route->getSlug();
				$name = $route->getName();
				# Prefix the route slug
				$slug = $this->join([ $prefix, $slug ]);
				$slug = sprintf('/%s', trim($slug, '/'));
				# Prefix the route name too
				$name = $this->join([ $namespace, $name ]);
				$name = str_replace('/', '.', trim($name, '/'));
				# Update slug and name, copy middleware from parent
				$clone = clone $route;
				$clone->setSlug($slug);
				$clone->setName($name);
				$flattened[] = $clone;
			}
		}
		# Nested groups
		$groups = $group->getGroups();
		if ( $groups ) {
			foreach ($groups as $subgroup) {
				$flattened = array_merge($flattened, $this->flatten($subgroup, $prefix, $namespace));
			}
		}
		return $flattened;
	}

	/**
	 * Join two or more path components
	 * @param  array  $parts     Parts of the path
	 * @param  string $separator Separator character
	 * @return string
	 */
	protected function join(array $parts, string $separator = '/'): string {
		$parts = array_map(function($part) use ($separator) {
			return call_user_func('trim', $part, $separator);
		}, $parts);
		return implode($separator, $parts);
	}
}
