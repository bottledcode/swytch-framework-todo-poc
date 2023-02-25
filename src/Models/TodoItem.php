<?php

namespace Bottledcode\SwytchFrameworkTodo\Models;

readonly class TodoItem
{
	public function __construct(
		public string $id,
		public string $userId,
		public string $todo,
		public bool $completed,
		public \DateTimeImmutable $created = new \DateTimeImmutable()
	) {
	}

	public function with(string|null $todo = null, bool|null $completed = null): self {
		return new self(
			$this->id,
			$this->userId,
			$todo ?? $this->todo,
			$completed ?? $this->completed,
			$this->created
		);
	}
}
