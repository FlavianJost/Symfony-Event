<?php

namespace App\Enum;

enum RoleUser : String
{
    case USER = 'ROLE_USER';
    case ORGANISER = 'ROLE_ORGANISER';
    case ADMIN = 'ROLE_ADMIN';
}
