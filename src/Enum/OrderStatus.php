<?php
namespace App\Enum;
enum OrderStatus: string{
    case enPreparation = 'en préparation';
    case expediee = "expédiée";
    case livree = "livrée";
    case annulee = "annulée";
}