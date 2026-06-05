<?php

namespace App\Enums;

enum AssetAssignmentType: string
{
    case User = 'user';
    case Branch = 'branch';
    case Employee = 'employee';
    case Department = 'department';
}
