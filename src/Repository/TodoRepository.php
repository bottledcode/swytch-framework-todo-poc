<?php

namespace Bottledcode\SwytchFrameworkTodo\Repository;

use Bottledcode\SwytchFrameworkTodo\Models\TodoItem;
use Symfony\Component\Serializer\Serializer;

class TodoRepository {
	/** @var TodoItem[] */
	private array $todos = [];

	private const FILENAME = '/tmp/todosv2.json';

	public function __construct(Serializer $serializer) {
		if(!file_exists(self::FILENAME)) {
			file_put_contents(self::FILENAME, '[]');
		}
		$todos = json_decode(file_get_contents(self::FILENAME), true);
		foreach($todos as $todo) {
			$this->todos[] = $serializer->denormalize($todo, TodoItem::class);
		}
	}

	public function getTodos(): array {
		return $this->todos;
	}

	public function getCompleted(bool $isCompleted): array {
		return array_filter($this->todos, fn(TodoItem $todo) => $todo->completed === $isCompleted);
	}

	public function add(TodoItem $todo): int {
		$this->todos[] = $todo;
		return count($this->todos) - 1;
	}

	public function save(): void {
		file_put_contents(self::FILENAME, json_encode($this->todos));
	}

	public function remove(int $index): TodoItem|null {
		$deleted = $this->todos[$index] ?? null;
		unset($this->todos[$index]);
		return $deleted;
	}

	public function count(): int {
		return count($this->todos);
	}

	public function update(int $index, TodoItem $todo): void {
		$this->todos[$index] = $todo;
	}

	public function get(int $id): TodoItem|null {
		return $this->todos[$id] ?? null;
	}
}
