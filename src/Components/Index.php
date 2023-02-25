<?php

namespace Bottledcode\SwytchFrameworkTodo\Components;

use Bottledcode\SwytchFramework\Template\Attributes\Component;
use Bottledcode\SwytchFramework\Template\Traits\RegularPHP;
use Bottledcode\SwytchFrameworkTodo\Language;

#[Component('Index')]
readonly class Index
{
	use RegularPHP;

	public function __construct(private Language $language)
	{
	}

	public function render()
	{
		$this->begin();
		?>
		<!DOCTYPE html>
		<html lang="<?= $this->language->currentLanguage ?>">
		<head>
			<meta charset="utf-8">
			<meta name="viewport" content="width=device-width, initial-scale=1">
			<title>{<?= __('TodoMVC') ?>}</title>
			<link rel="stylesheet" href="css/app.css">
		</head>
		<body>
		<Route path="/" method="GET" render="<App show='all' />"></Route>
		<Route path="/active" method="GET" render="<App show='active' />"></Route>
		<Route path="/completed" method="GET" render="<App show='completed' />"></Route>
		</body>
		</html>
		<?php
		return $this->end();
	}
}
