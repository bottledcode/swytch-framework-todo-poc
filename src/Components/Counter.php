<?php

namespace Bottledcode\SwytchFrameworkTodo\Components;

use Bottledcode\SwytchFramework\Template\Attributes\Component;
use Bottledcode\SwytchFramework\Template\Traits\RegularPHP;
use Bottledcode\SwytchFrameworkTodo\Repository\TodoRepository;

#[Component('Counter')]
readonly class Counter
{
	use RegularPHP;

	public function __construct(private TodoRepository $todos)
	{
	}

	public function render(bool $asOOB = false)
	{
		$uncompleted = $this->todos->getCompleted(false);
		$counted = count($uncompleted);

		$this->begin();
		?>
		<span class="todo-count" id="counter"><strong><?= n__(
					'%d item left',
					'%d items left',
					$counted,
					$counted
				) ?></strong></span>
		<?php
		return $this->end();
	}
}
