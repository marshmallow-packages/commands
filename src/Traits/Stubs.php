<?php

/**
 * Stubs trait
 *
 * PHP version 7.4
 *
 * @category Commands
 * @package  Traits
 * @author   Stef van Esch <stef@marshmallow.dev>
 * @license  MIT Licence
 * @link     https://marshmallow.dev
 */

namespace Marshmallow\Commands\Traits;

/**
 * Stubs trait is used to easly get and parse stub files
 *
 * @category Commands
 * @package  Traits
 * @author   Stef van Esch <stef@marshmallow.dev>
 * @license  MIT Licence
 * @link     https://marshmallow.dev
 */
trait Stubs
{
    /**
     * [getStub description]
     *
     * @param string $stub_name    name of the stub file
     * @param array  $replace_data params to be replaced
     *
     * @return string               parsed template
     */
    protected function getStub(string $stub_name, array $replace_data = [])
    {
        $stub_file = __dir__ . "/../stubs/$stub_name.stub";
        $stub_content = file_get_contents($stub_file);

        foreach ($replace_data as $search => $replace) {

            // With spaces
            $stub_content = str_replace(
                '{{ ' . $search . ' }}',
                $replace,
                $stub_content
            );

            // Without spaces
            $stub_content = str_replace(
                '{{' . $search . '}}',
                $replace,
                $stub_content
            );
        }
        return $stub_content;
    }
}
