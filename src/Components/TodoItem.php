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
class TodoItem
{
	use FancyClasses;
	use Htmx;
	use RegularPHP;

	public function __construct(private TodoRepository $todos, private Compiler $compiler)
	{
	}

	#[Route(Method::POST, '/api/todo/:id/toggle')]
	public function toggleComplete(string $target_id, string $id): string
	{
		$previous = $this->todos->get($id);
		if ($previous === null) {
			return '';
		}
		$this->todos->update($id, new TodoItemModel($previous->todo, !$previous->completed));
		$this->todos->save();
		return $this->rerender(
			$target_id,
			['todo' => $previous->todo, 'completed' => !$previous->completed, 'key' => $id],
			prependHtml: "<Counter hx-swap-oob='true' id='counter' />"
		);
	}

	#[Route(Method::DELETE, '/api/todo/:id')]
	public function deleteTodo(string $id, string $target_id): string
	{
		$this->todos->remove($id);
		$this->todos->save();
		$this->retarget('#' . $target_id);
		$this->reswap(HtmxSwap::OuterHtml);
		return $this->html("<Counter hx-swap-oob='true' id='counter' /><li class='destroyed'></li>");
	}

	#[Route(Method::POST, '/api/todo/:id/edit')]
	public function editTodo(string $target_id, array $state): string
	{
		return $this->rerender($target_id, [...$state, 'editing' => true]);
	}

	#[Route(Method::PATCH, '/api/todo/:id')]
	public function updateTodo(string $target_id, string $id, array $state, TodoItemModel $todo): string
	{
		$this->todos->update($id, new TodoItemModel($todo->todo, $this->todos->get($id)->completed));
		$this->todos->save();
		return $this->rerender(
			$target_id,
			[...$state, 'completed' => $todo->completed, 'todo' => $todo->todo, 'editing' => false]
		);
	}

	public function render(string $todo, bool $completed, int $key, bool $editing = false)
	{
		$this->begin();
		?>
		<li class="<?= $this->classNames(compact('completed', 'editing')) ?>">
			<form hx-patch="/api/todo/{<?= $key ?>}">
				<div class="view">
					<input type="hidden" name="completed" value="{{$completed}}">
					<input
							class="toggle"
							type="checkbox"
							{<?= $this->checked($completed) ?>}
							hx-post="/api/todo/{<?= $key ?>}/toggle"
					>
					<label hx-trigger="dblclick" hx-post="/api/todo/{<?= $key ?>}/edit">{<?= $todo ?>}</label>
					<button class="destroy" hx-delete="/api/todo/{<?= $key ?>}" type="button"></button>
				</div>
				<input name="todo" class="edit" value="{<?= $todo ?>}">
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
