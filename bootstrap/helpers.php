<?php
namespace Laradock;

function invoke($class)
{
    return $class();
}

function vendor_path($path)
{
    return base_path('vendor/'.$path);
}
