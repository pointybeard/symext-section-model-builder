<?php

declare(strict_types=1);

/*
 * This file is part of the "Section Model Builder for Symphony CMS" repository.
 *
 * Copyright 2020 Alannah Kearney <hi@alannahkearney.com>
 *
 * For the full copyright and license information, please view the LICENCE
 * file that was distributed with this source code.
 */

if (!file_exists(__DIR__.'/vendor/autoload.php')) {
    throw new Exception(sprintf('Could not find composer autoload file %s. Did you run `composer update` in %s?', __DIR__.'/vendor/autoload.php', __DIR__));
}

require_once __DIR__.'/vendor/autoload.php';

use pointybeard\Symphony\Extended;
use pointybeard\Symphony\SectionBuilder;

// Check if the class already exists before declaring it again.
if (!class_exists('\\Extension_SectionModelBuilder')) {
    final class Extension_SectionModelBuilder extends Extended\AbstractExtension
    {
    }
}
