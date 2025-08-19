<?php

function add($a, $b)
{
    if ($b == 0)
        return $a;
    return add($a + 1, $b - 1);
}

function multiply($a, $b)
{
    if ($b == 0)
        return 0;
    return add($a, multiply($a, $b - 1));
}

function factorial($n)
{
    if ($n == 0)
        return 1;
    return multiply($n, factorial($n - 1));
}

function power($base, $exp)
{
    if ($exp == 0)
        return 1;
    return multiply($base, power($base, $exp - 1));
}


echo multiply(10, 2);