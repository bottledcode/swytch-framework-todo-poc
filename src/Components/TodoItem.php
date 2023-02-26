<?php


namespace Bottledcode\SwytchFrameworkTodo\Components;

use Bottledcode\SwytchFramework\Router\Attributes\Route;
use Bottledcode\SwytchFramework\Router\Method;
use Bottledcode\SwytchFramework\Template\Attributes\Component;
use Bottledcode\SwytchFramework\Template\Compiler;
use Bottledcode\SwytchFramework\Template\Enum\HtmxSwap;
use Bottledcode\SwytchFramework\Template\Traits\FancyClasses;
use Bottledcode\SwytchFramework\Template\Traits\Htmx;
use Bottledcode\SwytchFramework\Template\Traits\RegularPHP;
use Bottledcode\SwytchFrameworkTodo\Models\TodoItem as TodoItemModel;
use Bottledcode\SwytchFrameworkTodo\Repository\TodoRepository;

#[Component('TodoItem')]
readonly class TodoItem
{
	use FancyClasses;
	use Htmx;
	use RegularPHP;

	public function __construct(private TodoRepository $todos, private Compiler $compiler)
	{
	}

	#[Route(Method::POST, '/api/todo/:id/toggle')]
	public function toggleComplete(string $target_id, string $id, array $state): string
	{
		$previous = $this->todos->get($id);
		if ($previous === null) {
			return '';
		}
		$new = $previous->with(completed: !$previous->completed);
		$this->todos->update($new);
		return $this->rerender(
			$target_id,
			[...$state, 'completed' => !$previous->completed],
			prependHtml: "<Counter hx-swap-oob='true' id='counter' />"
		);
	}

	#[Route(Method::DELETE, '/api/todo/:id')]
	public function deleteTodo(string $id, string $target_id): string
	{
		$this->todos->remove($id);
		$this->retarget('#' . $target_id);
		$this->reswap(HtmxSwap::OuterHtml);
		return $this->html("<Counter hx-swap-oob='true' id='counter' /><li style='border: none' class='destroyed'></li>");
	}

	#[Route(Method::POST, '/api/todo/:id/edit')]
	public function editTodo(string $target_id, array $state): string
	{
		return $this->rerender($target_id, [...$state, 'editing' => true]);
	}

	#[Route(Method::PATCH, '/api/todo/:id')]
	public function updateTodo(string $target_id, array $state, string $todo, bool $completed, string $id): string
	{
		$new = $this->todos->get($id)->with(todo: $todo, completed: $completed);
		$this->todos->update($new);
		return $this->rerender(
			$target_id,
			[...$state, 'completed' => $new->completed, 'todo' => $new->todo, 'editing' => false]
		);
	}

	public function render(string $todo, bool $completed, string $key, bool $editing = false)
	{
		$this->begin();
		?>
		<li class="<?= $this->classNames(compact('completed', 'editing')) ?>">
			<form hx-patch="/api/todo/{<?= $key ?>}">
				<div class="view">
					<input type="hidden" name="completed" value="{<?= $completed ?>}">
					<input
							class="toggle"
							type="checkbox"
							<?= $this->checked($completed) ?>
							hx-post="/api/todo/{<?= $key ?>}/toggle"
					>
					<label hx-trigger="dblclick" hx-post="/api/todo/{<?= $key ?>}/edit">{<?= $todo ?>}</label>
					<button class="destroy" hx-delete="/api/todo/{<?= $key ?>}" type="button"></button>
				</div>
				<input name="todo" <?= $editing ? 'autofocus' : '' ?> <?= $editing ? 'hx-trigger="blur"' : '' ?> class="edit" value="{<?= $todo ?>}">
			</form>
		</li>
		<?php
		return $this->end();
	}

	private function checked(bool $completed): string
	{
		return $completed ? 'checked' : '';
	}
}
