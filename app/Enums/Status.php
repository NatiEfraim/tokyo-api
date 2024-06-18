<?php

namespace App\Enums;

enum Status:int
{
        //
    case OK = 200;
    case NOT_FOUND = 404;
    case CREATED = 201;
    case BAD_REQUEST = 400;
    case UNAUTHORIZED = 401;
    case INTERNAL_SERVER_ERROR = 500;
    case CONFLICT = 409;
    case FORBIDDEN = 403;
    case NO_CONTENT = 204;
}
