<?php

namespace Bottledcode\SwytchFrameworkTodo\Components;

use Bottledcode\SwytchFramework\Router\Attributes\Route;
use Bottledcode\SwytchFramework\Router\Method;
use Bottledcode\SwytchFramework\Template\Attributes\Component;
use Bottledcode\SwytchFramework\Template\Compiler;
use Bottledcode\SwytchFramework\Template\Traits\Htmx;
use Bottledcode\SwytchFramework\Template\Traits\RegularPHP;
use Bottledcode\SwytchFrameworkTodo\Repository\TodoRepository;

#[Component('Main')]
readonly class Main
{
	use Htmx;
	use RegularPHP;

	public function __construct(private TodoRepository $todos, private Compiler $compiler)
	{
	}

	#[Route(Method::POST, '/api/todos/complete')]
	public function markAllCompleted(string $target_id, array $state): string
	{
		$complete = count($this->todos->getCompleted(false)) === 0;

		/**
		 * @var \Bottledcode\SwytchFrameworkTodo\Models\TodoItem $todo
		 */
		foreach ($this->todos->getTodos() as $id => $todo) {
			$this->todos->update($todo->with(completed: !$complete));
		}

		return $this->rerender($target_id, $state, prependHtml: "<Counter hx-swap-oob='true' id='counter' />");
	}

	public function render(string $filter)
	{
		$todoList = '';
		$todos = match ($filter) {
			'active' => $this->todos->getCompleted(false),
			'completed' => $this->todos->getCompleted(true),
			default => $this->todos->getTodos()
		};
		foreach ($todos as $key => $todo) {
			$todoList .= "<TodoItem todo='{{$todo->todo}}' completed='{{$todo->completed}}' key='{{$todo->id}}'/>\n";
		}

		$allCompleted = count($this->todos->getCompleted(false)) === 0 ? 'checked' : '';

		$this->begin();
		?>
		<section class="main" hx-sync="this:queue all">
			<form hx-post="/api/todos/complete">
				<input hx-post="/api/todos/complete" id="toggle-all" class="toggle-all" type="checkbox" <?= $allCompleted ?>>
				<label for="toggle-all">{<?= __('Mark all as complete') ?>}</label>
			</form>
			<ul class="todo-list">
				<?= $todoList ?>
			</ul>
		</section>
		<?php
		return $this->end();
	}
}
