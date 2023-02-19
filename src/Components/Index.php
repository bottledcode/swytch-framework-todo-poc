<?php

namespace Bottledcode\SwytchFrameworkTodo\Components;

use Bottledcode\SwytchFramework\Template\Attributes\Component;

#[Component('Index')]
class Index {
	public function render() {
		return <<<HTML
<!DOCTYPE html>
<html lang="en">
	<head>
		<meta charset="utf-8">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<title>TodoMVC</title>
		<link rel="stylesheet" href="css/app.css">
	</head>
	<body>
		<Route path="/" method="GET" render="<App show='all' />" />
		<Route path="/active" method="GET" render="<App show='active' />"/>
		<Route path="/completed" method="GET" render="<App show='completed' />"/>
	</body>
</html>
HTML;
	}
}
