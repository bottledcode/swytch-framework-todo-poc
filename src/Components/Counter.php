<?php

namespace Bottledcode\SwytchFrameworkTodo\Components;

use Bottledcode\SwytchFramework\Template\Attributes\Component;
use Bottledcode\SwytchFrameworkTodo\Repository\TodoRepository;

#[Component('Counter')]
class Counter {
	public function __construct(private TodoRepository $todos) {}

	public function render(bool $asOOB = false) {
		$uncompleted = $this->todos->getCompleted(false);
		$counted = count($uncompleted);
		$items = $counted === 1 ? 'item' : 'items';

		return <<<HTML
<span class="todo-count" id="counter"><strong>{{$counted}} $items left</strong></span>
HTML;
	}
}
