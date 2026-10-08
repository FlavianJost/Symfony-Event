<?php

namespace App\Enum;

enum RoleUser : String
{
    case USER = 'ROLE_USER';
    case ADMIN = 'ROLE_ADMIN';
    case ORGANISER = 'ROLE_ORGANISER';
}
