<?php
/**
 * Tam Tam (https://tamtam.com)
 *
 * @link https://github.com/tamtam/tamtam source repository
 *
 * @copyright Copyright (c) 2020. Tam Tam LLC (https://tamtam.com)
 *
 * @license https://opensource.org/licenses/AAL
 */

namespace App\Designs;

abstract class AbstractDesign
{
    abstract public function includes();

    abstract public function header();

    abstract public function body();

    abstract public function product();

    abstract public function task();

    abstract public function footer();

}
