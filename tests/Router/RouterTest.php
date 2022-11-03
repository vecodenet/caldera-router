<?php

declare(strict_types = 1);

/**
 * Caldera Router
 * Routing component with controllers and route groups, part of Vecode Caldera
 * @author  biohzrdmx <github.com/biohzrdmx>
 * @copyright Copyright (c) 2022 Vecode. All rights reserved
 */

namespace Caldera\Tests\Router;

use Exception;
use BadMethodCallException;
use RuntimeException;

use PHPUnit\Framework\TestCase;

use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Message\StreamInterface;

use Nyholm\Psr7\Factory\Psr17Factory;

use Caldera\Container\Container;
use Caldera\Router\Controller;
use Caldera\Router\Group;
use Caldera\Router\Route;
use Caldera\Router\Router;
use Caldera\Router\NotFoundException;

class RouterTest extends TestCase {

	/**
	 * Container instance
	 * @var Container
	 */
	protected static $container;

	/**
	 * ResponseFactoryInterface instance
	 * @var ResponseFactoryInterface
	 */
	protected static $factory;

	public static function setUpBeforeClass(): void {
		self::$factory = new Psr17Factory();
		self::$container = new Container();
		self::$container->add(ResponseFactoryInterface::class, true, self::$factory);
		self::$container->add(RequestFactoryInterface::class, true, self::$factory);
	}

	public function testAddGetRoute() {
		$router = new Router(self::$container, self::$factory);
		$route = $router->get('/foo', 'dummy')->setName('test.get');
		$this->assertInstanceOf(Route::class, $route);
		$routes = $router->getRoutes();
		$this->assertEquals([$route], $routes);
		$this->assertEquals(['GET', 'HEAD'], $route->getMethods());
		$this->assertEquals('test.get', $route->getName());
		$this->assertEquals('/foo', $route->getSlug());
		$this->assertEquals('dummy', $route->getHandler());
	}

	public function testAddPostRoute() {
		$router = new Router(self::$container, self::$factory);
		$route = $router->post('/foo', 'dummy')->setName('test.post');
		$this->assertInstanceOf(Route::class, $route);
		$routes = $router->getRoutes();
		$this->assertEquals([$route], $routes);
		$this->assertEquals(['POST'], $route->getMethods());
		$this->assertEquals('test.post', $route->getName());
		$this->assertEquals('/foo', $route->getSlug());
		$this->assertEquals('dummy', $route->getHandler());
	}

	public function testAddPutRoute() {
		$router = new Router(self::$container, self::$factory);
		$route = $router->put('/foo', 'dummy')->setName('test.put');
		$this->assertInstanceOf(Route::class, $route);
		$routes = $router->getRoutes();
		$this->assertEquals([$route], $routes);
		$this->assertEquals(['PUT'], $route->getMethods());
		$this->assertEquals('test.put', $route->getName());
		$this->assertEquals('/foo', $route->getSlug());
		$this->assertEquals('dummy', $route->getHandler());
	}

	public function testAddPatchRoute() {
		$router = new Router(self::$container, self::$factory);
		$route = $router->patch('/foo', 'dummy')->setName('test.patch');
		$this->assertInstanceOf(Route::class, $route);
		$routes = $router->getRoutes();
		$this->assertEquals([$route], $routes);
		$this->assertEquals(['PATCH'], $route->getMethods());
		$this->assertEquals('test.patch', $route->getName());
		$this->assertEquals('/foo', $route->getSlug());
		$this->assertEquals('dummy', $route->getHandler());
	}

	public function testAddOptionsRoute() {
		$router = new Router(self::$container, self::$factory);
		$route = $router->options('/foo', 'dummy')->setName('test.options');
		$this->assertInstanceOf(Route::class, $route);
		$routes = $router->getRoutes();
		$this->assertEquals([$route], $routes);
		$this->assertEquals(['OPTIONS'], $route->getMethods());
		$this->assertEquals('test.options', $route->getName());
		$this->assertEquals('/foo', $route->getSlug());
		$this->assertEquals('dummy', $route->getHandler());
	}

	public function testAddDeleteRoute() {
		$router = new Router(self::$container, self::$factory);
		$route = $router->delete('/foo', 'dummy')->setName('test.delete');
		$this->assertInstanceOf(Route::class, $route);
		$routes = $router->getRoutes();
		$this->assertEquals([$route], $routes);
		$this->assertEquals(['DELETE'], $route->getMethods());
		$this->assertEquals('test.delete', $route->getName());
		$this->assertEquals('/foo', $route->getSlug());
		$this->assertEquals('dummy', $route->getHandler());
	}

	public function testAddHeadRoute() {
		$router = new Router(self::$container, self::$factory);
		$route = $router->head('/foo', 'dummy')->setName('test.head');
		$this->assertInstanceOf(Route::class, $route);
		$routes = $router->getRoutes();
		$this->assertEquals([$route], $routes);
		$this->assertEquals(['HEAD'], $route->getMethods());
		$this->assertEquals('test.head', $route->getName());
		$this->assertEquals('/foo', $route->getSlug());
		$this->assertEquals('dummy', $route->getHandler());
	}

	public function testAddAnyRoute() {
		$router = new Router(self::$container, self::$factory);
		$route = $router->any('/foo', 'dummy')->setName('test.any');
		$this->assertInstanceOf(Route::class, $route);
		$routes = $router->getRoutes();
		$this->assertEquals([$route], $routes);
		$this->assertEquals(['*'], $route->getMethods());
		$this->assertEquals('test.any', $route->getName());
		$this->assertEquals('/foo', $route->getSlug());
		$this->assertEquals('dummy', $route->getHandler());
	}

	public function testAddMatchRoute() {
		$router = new Router(self::$container, self::$factory);
		$route = $router->match(['GET', 'POST'], '/foo', 'dummy')->setName('test.match');
		$this->assertInstanceOf(Route::class, $route);
		$routes = $router->getRoutes();
		$this->assertEquals([$route], $routes);
		$this->assertEquals(['GET', 'POST'], $route->getMethods());
		$this->assertEquals('test.match', $route->getName());
		$this->assertEquals('/foo', $route->getSlug());
		$this->assertEquals('dummy', $route->getHandler());
	}

	public function testAddRouteWithConstraint() {
		$router = new Router(self::$container, self::$factory);
		$route = $router->get('/user/{id}', 'dummy')
			->setName('test.get')
			->setConstraint('id', '\d+');
		$this->assertEquals('\d+', $route->getConstraint('id'));
	}

	public function testAddRouteInsert() {
		$router = new Router(self::$container, self::$factory);
		$router->get('/last', 'dummy')->setName('test.last');
		$router->get('/first', 'dummy', true)->setName('test.first');
		$routes = $router->getRoutes();
		$this->assertEquals('test.first', $routes[0]->getName());
	}

	public function testAddRouteGroup() {
		$router = new Router(self::$container, self::$factory);
		$group = $router->group('users', function(Group $group) {
			$group->get('/', 'dummy')->setName('index');
			$group->any('/new', 'dummy')->setName('new');
			$group->any('/edit/{id}', 'dummy')->setName('edit');
			$group->any('/delete/{id}', 'dummy')->setName('delete');
		})->setName('test.users');
		$groups = $router->getGroups();
		$this->assertIsArray($groups);
		$this->assertInstanceOf(Group::class, $group);
		$this->assertEquals('test.users', $group->getName());
		$this->assertEquals('users', $group->getPrefix());
		$this->assertIsArray($group->getRoutes());
		$this->assertCount(4, $group->getRoutes());
	}

	public function testDispatchInvalidHandler() {
		$router = new Router(self::$container, self::$factory);
		$router->setDefault('/home');
		$router->get('/home', ['foo'])->setName('test.home');
		$request_factory = self::$container[RequestFactoryInterface::class];
		$request = $request_factory->createServerRequest('GET', 'http://localhost/');
		try {
			$router->dispatch($request);
			$this->fail('This must throw a RuntimeException');
		} catch (Exception $e) {
			$this->assertInstanceOf(RuntimeException::class, $e);
			$this->assertEquals("Invalid handler specified", $e->getMessage());
		}
	}

	public function testDispatchInvalidParameter() {
		$router = new Router(self::$container, self::$factory);
		$router->setDefault('/home');
		$router->get('/home', function(ServerRequestInterface $request, ResponseInterface $response, StreamInterface $stream) {
			$response->getBody()->write('HOME');
		})->setName('test.home');
		$request_factory = self::$container[RequestFactoryInterface::class];
		$request = $request_factory->createServerRequest('GET', 'http://localhost/');
		try {
			$router->dispatch($request);
			$this->fail('This must throw a RuntimeException');
		} catch (Exception $e) {
			$this->assertInstanceOf(RuntimeException::class, $e);
			$this->assertEquals("Can not resolve parameter 'stream'", $e->getMessage());
		}
	}

	public function testDispatchNonExisting() {
		$router = new Router(self::$container, self::$factory);
		$router->setDefault('/home');
		$router->get('/home', function(ServerRequestInterface $request, ResponseInterface $response) {
			$response->getBody()->write('HOME');
		})->setName('test.home');
		# Try a non-existing route
		$request_factory = self::$container[RequestFactoryInterface::class];
		$request = $request_factory->createServerRequest('GET', 'http://localhost/contact');
		try {
			$router->dispatch($request);
			$this->fail('This must throw a NotFoundException');
		} catch (Exception $e) {
			$this->assertInstanceOf(NotFoundException::class, $e);
		}
	}

	public function testDispatchSimple() {
		$router = new Router(self::$container, self::$factory);
		$router->setDefault('/home');
		$router->get('/home', function(ServerRequestInterface $request, ResponseInterface $response) {
			$response->getBody()->write('HOME');
		})->setName('test.home');
		$router->get('/about', function(ServerRequestInterface $request, ResponseInterface $response) {
			$response->getBody()->write('ABOUT');
		})->setName('test.about');
		$this->assertEquals('/home', $router->getDefault());
		$request_factory = self::$container[RequestFactoryInterface::class];
		# Try with an existing route
		$request = $request_factory->createServerRequest('GET', 'http://localhost/');
		$response = $router->dispatch($request);
		$this->assertInstanceOf(ResponseInterface::class, $response);
		$this->assertEquals('HOME', (string) $response->getBody());
		# Try with an existing route
		$request = $request_factory->createServerRequest('GET', 'http://localhost/about');
		$response = $router->dispatch($request);
		$this->assertInstanceOf(ResponseInterface::class, $response);
		$this->assertEquals('ABOUT', (string) $response->getBody());
	}

	public function testDispatchParameters() {
		$router = new Router(self::$container, self::$factory);
		$router->get('/users/edit/{id}/{opt?}', function(ServerRequestInterface $request, ResponseInterface $response, ResponseFactoryInterface $factory, int $id, string $opt = 'foo') {
			$response = $factory->createResponse(210);
			$response->getBody()->write( sprintf('%d:%s', $id, $opt) );
			return $response;
		})->setName('users.edit');
		$request_factory = self::$container[RequestFactoryInterface::class];
		# Try with an parameter route
		$request = $request_factory->createServerRequest('GET', 'http://localhost/users/edit/215');
		$response = $router->dispatch($request);
		$this->assertInstanceOf(ResponseInterface::class, $response);
		$this->assertEquals('215:foo', (string) $response->getBody());
		# Try with an parameter route
		$request = $request_factory->createServerRequest('GET', 'http://localhost/users/edit/215/bar');
		$response = $router->dispatch($request);
		$this->assertInstanceOf(ResponseInterface::class, $response);
		$this->assertEquals('215:bar', (string) $response->getBody());
	}

	public function testSetDirectory() {
		$router = new Router(self::$container, self::$factory);
		$router->setDefault('/home');
		$router->setDirectory('/some/directory/structure/');
		$router->get('/home', function(ServerRequestInterface $request, ResponseInterface $response) {
			$response->getBody()->write('HOME');
		})->setName('test.home');
		$router->get('/about', function(ServerRequestInterface $request, ResponseInterface $response) {
			$response->getBody()->write('ABOUT');
		})->setName('test.about');
		$this->assertEquals('some/directory/structure', $router->getDirectory());
		$request_factory = self::$container[RequestFactoryInterface::class];
		# Try with an existing route
		$request = $request_factory->createServerRequest('GET', 'http://localhost/some/directory/structure');
		$response = $router->dispatch($request);
		$this->assertInstanceOf(ResponseInterface::class, $response);
		$this->assertEquals('HOME', (string) $response->getBody());
		# Try with an existing route
		$request = $request_factory->createServerRequest('GET', 'https://localhost/some/directory/structure/about');
		$response = $router->dispatch($request);
		$this->assertInstanceOf(ResponseInterface::class, $response);
		$this->assertEquals('ABOUT', (string) $response->getBody());
	}

	public function testControllerInvalidMethod() {
		$controller = new TestController();
		try {
			$controller->list();
			$this->fail('This must throw a BadMethodCallException');
		} catch (Exception $e) {
			$this->assertInstanceOf(BadMethodCallException::class, $e);
		}
	}

	public function testDispatchGroupWithController() {
		$router = new Router(self::$container, self::$factory);
		$router->setDirectory('/some/directory/structure/');
		$router->group('users', function(Group $group) {
			$group->get('/', [TestController::class, 'index'])->setName('index');
			$group->any('/new', [TestController::class, 'new'])->setName('new');
			$group->any('/edit/{id}', [TestController::class, 'edit'])->setName('edit');
			$group->any('/delete/{id}', [TestController::class, 'delete'])->setName('delete');
		})->setName('test.users');
		$request_factory = self::$container[RequestFactoryInterface::class];
		$request = $request_factory->createServerRequest('GET', 'http://localhost/some/directory/structure/users');
		$response = $router->dispatch($request);
		$this->assertEquals('USERS.INDEX', (string) $response->getBody());
		$request = $request_factory->createServerRequest('GET', 'http://localhost/some/directory/structure/users/new');
		$response = $router->dispatch($request);
		$this->assertEquals('USERS.NEW', (string) $response->getBody());
		$request = $request_factory->createServerRequest('POST', 'http://localhost/some/directory/structure/users/edit/134');
		$response = $router->dispatch($request);
		$this->assertEquals('USERS.EDIT:134', (string) $response->getBody());
		$request = $request_factory->createServerRequest('POST', 'http://localhost/some/directory/structure/users/delete/625');
		$response = $router->dispatch($request);
		$this->assertEquals('USERS.DELETE:625', (string) $response->getBody());
	}

	public function testDispatchNestedGroupWithController() {
		$router = new Router(self::$container, self::$factory);
		$router->setDirectory('/some/directory/structure/');
		$router->group('admin', function(Group $group) {
			$group->group('users', function(Group $group) {
				$group->get('/', [TestController::class, 'index'])->setName('index');
				$group->any('/new', [TestController::class, 'new'])->setName('new');
				$group->any('/edit/{id}', [TestController::class, 'edit'])->setName('edit');
				$group->any('/delete/{id}', [TestController::class, 'delete'])->setName('delete');
			})->setName('users');
		})->setName('admin');
		$request_factory = self::$container[RequestFactoryInterface::class];
		# This must throw an exception
		$request = $request_factory->createServerRequest('GET', 'http://localhost/some/directory/structure/users');
		try {
			$router->dispatch($request);
			$this->fail('This must throw a NotFoundException');
		} catch (Exception $e) {
			$this->assertInstanceOf(NotFoundException::class, $e);
		}
		# These must work
		$request = $request_factory->createServerRequest('GET', 'http://localhost/some/directory/structure/admin/users');
		$response = $router->dispatch($request);
		$this->assertEquals('USERS.INDEX', (string) $response->getBody());
		$request = $request_factory->createServerRequest('GET', 'http://localhost/some/directory/structure/admin/users/new');
		$response = $router->dispatch($request);
		$this->assertEquals('USERS.NEW', (string) $response->getBody());
		$request = $request_factory->createServerRequest('POST', 'http://localhost/some/directory/structure/admin/users/edit/134');
		$response = $router->dispatch($request);
		$this->assertEquals('USERS.EDIT:134', (string) $response->getBody());
		$request = $request_factory->createServerRequest('POST', 'http://localhost/some/directory/structure/admin/users/delete/625');
		$response = $router->dispatch($request);
		$this->assertEquals('USERS.DELETE:625', (string) $response->getBody());
	}

	public function testReverseRouting() {
		$router = new Router(self::$container, self::$factory);
		$router->setDirectory('/some/directory/structure/');
		$router->get('/index', function() {
			# Dummy handler
		})->setName('index');
		$router->get('/about', function() {
			# Dummy handler
		})->setName('about');
		$router->group('admin', function(Group $group) {
			$group->group('users', function(Group $group) {
				$group->get('/', [TestController::class, 'index'])->setName('index');
				$group->any('/new', [TestController::class, 'new'])->setName('new');
				$group->any('/edit/{id}', [TestController::class, 'edit'])->setName('edit');
				$group->any('/delete/{id}', [TestController::class, 'delete'])->setName('delete');
			})->setName('users');
		})->setName('admin');
		# Start with the simple ones
		$route = $router->route('index');
		$this->assertEquals('/index', $route);
		$route = $router->route('about');
		$this->assertEquals('/about', $route);
		$route = $router->route('admin.users.index');
		$this->assertEquals('/admin/users', $route);
		$route = $router->route('admin.users.new');
		$this->assertEquals('/admin/users/new', $route);
		# With non-existing route
		try {
			$route = $router->route('foo.bar.baz');
			$this->fail('This must throw a RuntimeException');
		} catch (Exception $e) {
			$this->assertInstanceOf(RuntimeException::class, $e);
		}
		# Now the complex ones
		try {
			$route = $router->route('admin.users.edit');
			$this->fail('This must throw a RuntimeException');
		} catch (Exception $e) {
			$this->assertInstanceOf(RuntimeException::class, $e);
		}
		$route = $router->route('admin.users.new', ['ref' => 123]);
		$this->assertEquals('/admin/users/new?ref=123', $route);
		$route = $router->route('admin.users.edit', ['id' => 123]);
		$this->assertEquals('/admin/users/edit/123', $route);
		$route = $router->route('admin.users.delete', ['id' => 456]);
		$this->assertEquals('/admin/users/delete/456', $route);
	}
}

class TestController extends Controller {

	public function index(ServerRequestInterface $request, ResponseInterface $response) {
		$response->getBody()->write('USERS.INDEX');
	}
	public function new(ServerRequestInterface $request, ResponseInterface $response) {
		$response->getBody()->write('USERS.NEW');
	}
	public function edit(ServerRequestInterface $request, ResponseInterface $response, int $id) {
		$response->getBody()->write( sprintf('USERS.EDIT:%d', $id) );
	}
	public function delete(ServerRequestInterface $request, ResponseInterface $response, int $id) {
		$response->getBody()->write( sprintf('USERS.DELETE:%d', $id) );
	}
}
