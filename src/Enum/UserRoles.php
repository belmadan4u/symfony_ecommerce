<?php
namespace App\Enum;

enum UserRoles: string{
    case admin = "ROLE_ADMIN";
    case customer = "ROLE_CUSTOMER";
}