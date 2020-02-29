<?php

namespace App\Transformations;

use App\Address;
use App\ClientContact;
use App\Customer;
use App\Design;
use App\Repositories\ClientContactRepository;
use App\Repositories\CustomerRepository;

trait DesignTransformable
{

    /**
     * @param Address $address
     * @return Address
     */
    public function transformDesign(Design $design)
    {
        $obj = new Design();
        $obj->id = (int)$design->id;
        $obj->name = (string)$design->name;
        $obj->is_custom = (bool)$design->is_custom;
        $obj->is_active = (bool)$design->is_active;
        $obj->design = $design->design;
        return $obj;
    }

}
