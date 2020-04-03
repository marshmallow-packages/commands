<?php

namespace Marshmallow\Commands\App\Traits;

trait Stubs
{
	protected function getStub ($stub_name, $replace_data)
	{
		$stub_file = __dir__ . "/../../stubs/$stub_name.stub";
		$stub_content = file_get_contents($stub_file);

		foreach ($replace_data as $search => $replace) {
			$stub_content = str_replace('{{ ' . $search . ' }}', $replace, $stub_content);
			$stub_content = str_replace('{{' . $search . '}}', $replace, $stub_content);
		}
		return $stub_content;
	}
}