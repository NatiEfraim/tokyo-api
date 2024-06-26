<?php

namespace App\Services\Client;

use App\Enums\Status;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\Client;
use App\Enums\EmployeeType;



class ClientService{




    const MIN_LEN = 1;
    const MAX_LEN = 7;

    /**
     * fetch all clients records from users table.
     **/

     public function fetchCleintsRecords()
     {

        try {

            // Fetch users with their employeeType and roles
            $clients = Client::select('id', 'name', 'emp_type_id', 'personal_number')
            ->where('is_deleted', false)
                ->get();

            $clients->each(function ($client) {

                if ($client->emp_type_id) {

                    //? set and format poplution for each client records

                    $client->population = match ($client->emp_type_id) {
                        EmployeeType::KEVA->value, EmployeeType::SADIR->value => 's' . $client->personal_number,
                        EmployeeType::MILUIM->value => 'm' . $client->personal_number,
                        EmployeeType::OVED_TZAHAL->value => 'c' . $client->personal_number,
                        default => throw new \InvalidArgumentException('סוג עובד לא תקין.')
                    };
                }
                $client->makeHidden(['personal_number', 'emp_type_id']);

                return $client;
            });


            return [
                'status' => Status::OK,
                'data' => $clients->isEmpty() ? [] : $clients,
            ];

        } catch (\Exception $e) {

            Log::error($e->getMessage());
        }

        return [
            'status' => Status::INTERNAL_SERVER_ERROR,
            'message' => 'התרחש בעיית שרת יש לנסות שוב מאוחר יותר.',
        ];
    }

    /**
     * search clients records from based on personal_number or based on name.
     **/

    public function searchClients(Request $request)
    {
        try {




            $searchQuery = $request->input('query');

            $searchQuery = str_replace(' ', '', $request->input('query'));

            if ((ctype_digit($searchQuery) == true) && (strlen($searchQuery) < self::MIN_LEN || strlen($searchQuery) > self::MAX_LEN)) {


                return [
                    'status' => Status::BAD_REQUEST,
                    'message' => 'נתונים שנשלחו אינם תקינים.',
                ];

            }

            if ((ctype_digit($searchQuery) == true)) {

                //? search user by personal_number
                $clientsRecords = Client::with(['employeeType'])
                    ->where('personal_number', 'like', '%' . $searchQuery . '%')
                    ->where('is_deleted', false)
                    ->orderBy('id', 'asc')
                    ->get();

                $clientsRecords->each(function ($client) {

                    if ($client->emp_type_id) {

                        //? set and format poplution for each client records

                        $client->population = match ($client->emp_type_id) {
                            EmployeeType::KEVA->value, EmployeeType::SADIR->value => 's' . $client->personal_number,
                            EmployeeType::MILUIM->value => 'm' . $client->personal_number,
                            EmployeeType::OVED_TZAHAL->value => 'c' . $client->personal_number,
                            default => throw new \InvalidArgumentException('סוג עובד לא תקין.')
                        };
                    }
                    $client->makeHidden(['personal_number', 'emp_type_id']);

                    return $client;
                });

                return [
                    'status' => Status::OK,
                    'data' => $clientsRecords->isEmpty() ? [] : $clientsRecords,
                ];
            }


            // Search users by name (ignoring spaces)
            $clientsRecords = Client::with(['employeeType'])
                ->whereRaw("REPLACE(name, ' ', '') LIKE ?", ['%' . $searchQuery . '%'])
                ->where('is_deleted', false)
                ->orderBy('id', 'asc')
                ->get();



            $clientsRecords->each(function ($client) {

                if ($client->emp_type_id) {

                    //? set and format poplution for each client records

                    $client->population = match ($client->emp_type_id) {
                        EmployeeType::KEVA->value, EmployeeType::SADIR->value => 's' . $client->personal_number,
                        EmployeeType::MILUIM->value => 'm' . $client->personal_number,
                        EmployeeType::OVED_TZAHAL->value => 'c' . $client->personal_number,
                        default => throw new \InvalidArgumentException('סוג עובד לא תקין.')
                    };
                }
                
                $client->makeHidden(['personal_number', 'emp_type_id']);

                return $client;
            });

            return [
                'status' => Status::OK,
                'data' => $clientsRecords->isEmpty() ? [] : $clientsRecords,
            ];

        } catch (\Exception $e) {
            Log::error($e->getMessage());
        }

        return [
            'status' => Status::INTERNAL_SERVER_ERROR,
            'message' => 'התרחש בעיית שרת יש לנסות שוב מאוחר יותר.'
        ];
    }


}
