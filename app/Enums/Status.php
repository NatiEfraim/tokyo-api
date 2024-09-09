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
    case UNPROCESSABLE_ENTITY = 422;
    case CONFLICT = 409;
    case FORBIDDEN = 403;
    case NO_CONTENT = 204;


        /**
     * Get the Hebrew message for each status error.
     *
     * @return string
     */
    public function getHebrewMessage(): string
    {
        return match ($this) {
            self::OK => 'בקשה הושלמה בהצלחה.',
            self::NOT_FOUND => 'בקשה לא נמצאה.',
            self::CREATED => 'הנתונים נשמרו במערכת.',
            self::BAD_REQUEST => 'בקשה שגויה.',
            self::UNAUTHORIZED => 'הגישה נדחתה. יש להתחבר למערכת',
            self::INTERNAL_SERVER_ERROR => 'שגיאה פנימית בשרת.',
            self::CONFLICT => 'קיימת סתירה בנתונים.',
            self::FORBIDDEN => 'משתמש אינו מורשה לבצע בקשה זו.',
            self::NO_CONTENT => 'תוכן לא נמצא במערכת.',
            self::UNPROCESSABLE_ENTITY => 'נתונים שנשלחו אינם תקינים.',
        };
    }




}
