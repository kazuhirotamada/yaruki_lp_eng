<?php
    // app/sections.php
    $__sections = [];

    function start_section(string $name): void { ob_start(); }

    function end_section(string $name): void {
        global $__sections;
        $__sections[$name] = ($__sections[$name] ?? '') . ob_get_clean();
    }

    function section(string $name, string $default = ''): string {
        global $__sections;
        return $default . ($__sections[$name] ?? '');
    }