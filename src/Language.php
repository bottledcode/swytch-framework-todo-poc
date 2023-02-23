<?php

namespace Bottledcode\SwytchFrameworkTodo;

use Bottledcode\SwytchFramework\Language\LanguageAcceptor;

class Language extends LanguageAcceptor
{
	protected function getLanguageDir(): string
	{
		return __DIR__ . '/../translations';
	}
}
