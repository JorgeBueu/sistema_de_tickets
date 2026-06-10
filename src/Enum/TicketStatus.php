<?php

namespace App\Enum;

enum TicketStatus: string
{
    case OPEN = 'Abierto';
    case CLOSED = 'Cerrado';
}