<?php

namespace Bottledcode\SwytchFrameworkTodo\Components;

use Bottledcode\SwytchFramework\Template\Attributes\Component;
use Bottledcode\SwytchFramework\Template\Compiler;
use Bottledcode\SwytchFramework\Template\Traits\FancyClasses;
use Bottledcode\SwytchFramework\Template\Traits\Htmx;
use Bottledcode\SwytchFramework\Template\Traits\RegularPHP;
use Bottledcode\SwytchFrameworkTodo\Repository\TodoRepository;

#[Component('Footer')]
readonly class Footer
{
	use Htmx;
	use FancyClasses;
	use RegularPHP;

	public function __construct(private TodoRepository $todos, private Compiler $compiler)
	{
	}

	public function render(string $filter)
	{
		$this->begin();
		?>
		<Counter id="counter"></Counter>
		<ul class="filters">
			<li><a href="/" class="<?= $filter === 'all' ? 'selected' : '' ?>">All</a></li>
			<li><a href="/active" class="<?= $filter === 'active' ? 'selected' : '' ?>">Active</a></li>
			<li><a href="/completed" class="<?= $filter === 'completed' ? 'selected' : '' ?>">Completed</a></li>
		</ul>
		TIMING_PLACEHOLDER
		<?php
		return $this->end();
	}
}
