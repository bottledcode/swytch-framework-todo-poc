<?php

namespace Bottledcode\SwytchFrameworkTodo\Models;

// todo: allow passing enums
abstract class Filter
{
	public const ALL = 'all';
	public const ACTIVE = 'active';
	public const COMPLETED = 'completed';
}
