<?php

namespace Bottledcode\SwytchFrameworkTodo\Components;

use Bottledcode\SwytchFramework\Template\Attributes\Component;
use Bottledcode\SwytchFramework\Template\Compiler;
use Bottledcode\SwytchFramework\Template\Traits\FancyClasses;
use Bottledcode\SwytchFramework\Template\Traits\Htmx;
use Bottledcode\SwytchFrameworkTodo\Repository\TodoRepository;

#[Component('Footer')]
class Footer
{
	use Htmx;
	use FancyClasses;

	public function __construct(private TodoRepository $todos, private Compiler $compiler)
	{
	}

	public function render(string $filter)
	{
		$selectedAll = $filter === 'all' ? 'selected' : '';
		$selectedActive = $filter === 'active' ? 'selected' : '';
		$selectedCompleted = $filter === 'completed' ? 'selected' : '';

		return <<<HTML
<Counter id="counter"></Counter>
<ul class="filters">
	<li><a href="/" class="{{$selectedAll}}">All</a></li>
	<li><a href="/active" class="{{$selectedActive}}">Active</a></li>
	<li><a href="/completed" class="{{$selectedCompleted}}">Completed</a></li>
</ul>
HTML;

	}
}
