<?php
namespace App\Enum;
enum ProductStatus: string{
    case disponible = "disponible";
    case rupture = "en rupture";
    case precommande = "en précommande";
}