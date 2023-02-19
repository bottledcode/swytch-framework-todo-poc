<?php

namespace Bottledcode\SwytchFrameworkTodo\Components;

use Bottledcode\SwytchFramework\Router\Attributes\Route;
use Bottledcode\SwytchFramework\Router\Method;
use Bottledcode\SwytchFramework\Template\Attributes\Component;
use Bottledcode\SwytchFramework\Template\Compiler;
use Bottledcode\SwytchFramework\Template\Traits\Callbacks;
use Bottledcode\SwytchFrameworkTodo\Models\NewTodo;

#[Component('App')]
class App
{
	use Callbacks;

	private array $todos = [];

	#[Route(Method::POST, '/api/todo')]
	public function createTodo(NewTodo $todo, array $state, string $target_id): string
	{
		$this->todos[] = $todo->todo;
		file_put_contents('/tmp/todos.json', json_encode($this->todos));

		return $this->rerender($target_id, $state);
	}

	public function __construct(private Compiler $compiler) {
		if(!file_exists('/tmp/todos.json')) {
			file_put_contents('/tmp/todos.json', json_encode(['@!example todo']));
		}
		$this->todos = json_decode(file_get_contents('/tmp/todos.json'), true);
	}

	public function render(string $show)
	{
		$todoItems = array_map(fn($todo, $idx) => <<<HTML
<TodoItem key="{{$idx}}" value="{{$todo}}" />
HTML, $this->todos, array_keys($this->todos));

		$todoItems = implode("\n", $todoItems);

		return <<<HTML
<section class="todoapp">
	<header class="header">
		<h1>todos</h1>
		<form hx-post="/api/todo">
			<input type="hidden" name="csrf" value="{{csrf}}" >
			<input name="todo" class="new-todo" required placeholder="What needs to be done?" autofocus>
		</form>
	</header>
	$todoItems
</section>
HTML;
	}
}
