<?php

namespace Bottledcode\SwytchFrameworkTodo\Repository;

use Bottledcode\SwytchFrameworkTodo\Models\TodoItem;
use r\Connection;
use r\Cursor;
use r\Options\GetAllOptions;
use Symfony\Component\Serializer\Serializer;

use function r\row;
use function r\table;
use function r\uuid;

readonly class TodoRepository
{
	private string $userId;

	public function __construct(private Serializer $serializer, private Connection $connection)
	{
		$this->userId = explode(
			':',
			$_SERVER['HTTP_X_AUTH_REQUEST_USER'] ?? throw new \LogicException('No user id detected'),
			2
		)[0];
	}

	public function getTodos(): array
	{
		$results = table('todos')
			->getAll($this->userId, new GetAllOptions(index: 'userId'))
			->orderBy('created')
			->run($this->connection);
		return $this->denormalize($results);
	}

	private function denormalize(array|Cursor $items): array
	{
		$return = [];
		foreach ($items as $result) {
			$return[] = $this->serializer->denormalize($result, TodoItem::class);
		}
		return $return;
	}

	public function getCompleted(bool $isCompleted): array
	{
		$result = table('todos')
			->getAll([$this->userId, $isCompleted], new GetAllOptions(index: 'userIdAndCompleted'))
			->orderBy('created')
			->run($this->connection);
		return $this->denormalize($result);
	}

	public function add(TodoItem $todo): int
	{
		$actual = (array)$todo;
		$actual['userId'] = $this->userId;
		table('todos')->insert($actual)->run($this->connection);
		return table('todos')
			->getAll($this->userId, new GetAllOptions(index: 'userId'))
			->count()
			->run($this->connection);
	}

	public function count(): int
	{
		return table('todos')
			->getAll($this->userId, new GetAllOptions(index: 'userId'))
			->count()
			->run($this->connection);
	}

	public function remove(string $id): void
	{
		table('todos')
			->getAll([$this->userId, $id], new GetAllOptions(index: 'userIdId'))
			->delete()
			->run($this->connection);
	}

	public function get(string $id): TodoItem|null
	{
		$result = table('todos')
			->getAll([$this->userId, $id], new GetAllOptions(index: 'userIdId'))
			->run($this->connection);
		$result = $this->denormalize($result)[0] ?? null;
		if ($result !== null && $result->userId !== $this->userId) {
			throw new \LogicException('User does not own this todo');
		}
		return $result;
	}

	public function update(TodoItem $todo): void
	{
		$actual = (array)$todo;
		$actual['userId'] = $this->userId;
		table('todos')
			->getAll([$this->userId, $todo->id], new GetAllOptions('userIdId'))
			->update($actual)
			->run($this->connection);
	}

	public function newId(): string
	{
		return uuid()->run($this->connection);
	}
}
