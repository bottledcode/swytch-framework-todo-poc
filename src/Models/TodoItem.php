<?php

namespace Bottledcode\SwytchFrameworkTodo\Models;

readonly class TodoItem
{
	public function __construct(public string $todo, public bool $completed)
	{
	}
}
