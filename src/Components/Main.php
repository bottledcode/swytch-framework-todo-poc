<?php

namespace Bottledcode\SwytchFrameworkTodo\Components;

use Bottledcode\SwytchFramework\Router\Attributes\Route;
use Bottledcode\SwytchFramework\Router\Method;
use Bottledcode\SwytchFramework\Template\Attributes\Component;
use Bottledcode\SwytchFramework\Template\Compiler;
use Bottledcode\SwytchFramework\Template\Traits\Htmx;
use Bottledcode\SwytchFrameworkTodo\Repository\TodoRepository;

#[Component('Main')]
class Main {
	use Htmx;

	public function __construct(private TodoRepository $todos, private Compiler $compiler) {
	}

	#[Route(Method::POST, '/api/todos/complete')]
	public function markAllCompleted(string $target_id, array $state): string {
		foreach($this->todos->getTodos() as $id => $todo) {
			$this->todos->update($id, new \Bottledcode\SwytchFrameworkTodo\Models\TodoItem($todo->todo, true));
		}
		$this->todos->save();

		return $this->rerender($target_id, $state);
	}

	public function render(string $filter) {
		$todoList = '';
		$todos = match ($filter) {
			'active' => $this->todos->getCompleted(false),
			'completed' => $this->todos->getCompleted(true),
			default => $this->todos->getTodos()
		};
		foreach($todos as $key => $todo) {
			$todoList .= "<TodoItem todo='{$todo->todo}' completed='{$todo->completed}' key='{$key}'/>\n";
		}

		$allCompleted = count($this->todos->getCompleted(false)) === 0 ? 'checked' : '';

		return <<<HTML
<section class="main">
	<form hx-post="/api/todos/complete">
		<input hx-post="/api/todos/complete" id="toggle-all" class="toggle-all" type="checkbox" $allCompleted>
		<label for="toggle-all">Mark all as complete</label>
	</form>
	<ul class="todo-list">
	{$todoList}
	</ul>
</section>
HTML;

	}
}
