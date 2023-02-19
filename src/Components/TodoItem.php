<?php

namespace Bottledcode\SwytchFrameworkTodo\Components;

use Bottledcode\SwytchFramework\Template\Attributes\Component;

#[Component('TodoItem')]
class TodoItem {
	public function render(string $key, string $value) {
		return "<p>{{$value}}</p>";
	}
}
