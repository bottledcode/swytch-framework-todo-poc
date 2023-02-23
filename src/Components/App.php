<?php

namespace Bottledcode\SwytchFrameworkTodo\Components;

use Bottledcode\SwytchFramework\Router\Attributes\Route;
use Bottledcode\SwytchFramework\Router\Method;
use Bottledcode\SwytchFramework\Template\Attributes\Component;
use Bottledcode\SwytchFramework\Template\Compiler;
use Bottledcode\SwytchFramework\Template\Enum\HtmxSwap;
use Bottledcode\SwytchFramework\Template\Traits\Htmx;
use Bottledcode\SwytchFramework\Template\Traits\RegularPHP;
use Bottledcode\SwytchFrameworkTodo\Models\NewTodo;
use Bottledcode\SwytchFrameworkTodo\Repository\TodoRepository;

#[Component('App')]
class App
{
	use Htmx;
	use RegularPHP;

	public function __construct(private Compiler $compiler, private TodoRepository $todoRepository)
	{
	}

	#[Route(Method::POST, '/api/todo')]
	public function createTodo(NewTodo $todo, array $state, string $target_id): string
	{
		$id = $this->todoRepository->add(new \Bottledcode\SwytchFrameworkTodo\Models\TodoItem($todo->todo, false));
		$this->todoRepository->save();
		if ($this->todoRepository->count() === 1) {
			return $this->rerender($target_id, $state, '<Counter asOOB="true" />');
		}

		$this->retarget('.todo-list');
		$this->reswap(HtmxSwap::BeforeEnd);

		$this->begin();
		?>
		<input
				name="todo"
				class="new-todo"
				required
				placeholder="{<?= __('What needs to be done?') ?>}"
				autofocus
				hx-swap-oob="true"
				id="new-todo"
		>
		<Counter id="counter" hx-swap-oob="true"></Counter>
		<TodoItem todo="{<?= $todo->todo ?>}" completed="" key="{<?= $id ?>}"></TodoItem>
		<?php
		return $this->html($this->end());
	}

	public function render(string $show)
	{
		$hasTodos = $this->todoRepository->count() ? <<<HTML
<main filter="{{$show}}" ></main>
<footer class="footer" filter="{{$show}}"></footer>
HTML: '';


		$this->begin();
		?>
		<section class="todoapp">
			<header class="header">
				<h1><?= __('todos') ?></h1>
				<form hx-post="/api/todo">
					<input
							id="new-todo"
							name="todo"
							class="new-todo"
							required
							placeholder="<?= __('What needs to be done?') ?>"
							autofocus
					>
				</form>
			</header>
			<?= $hasTodos ?>
		</section>
		<?php
		return $this->end();
	}
}
