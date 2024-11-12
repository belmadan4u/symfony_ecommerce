<?php
namespace App\Enum;

enum UserRoles: string{
    case admin = "ADMIN";
    case customer = "CUSTOMER";
}