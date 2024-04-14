<?php

namespace App\Http\Controllers;

use App\Mail\DistributionMail;
use App\Mail\InventoryMail;
use App\Mail\UserMail;
use App\Models\Distribution;
use App\Models\Inventory;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Illuminate\Support\Facades\Mail;

use Illuminate\Support\Facades\Validator;

class ExportController extends Controller
{
    //

    /**
     * Export inventories data to Excel file.
     *
     * Exports inventories data to an Excel file based on the selected year and month.
     *
     * @OA\Post(
     *     path="/api/export/inventories",
     *     tags={"Export"},
     *     summary="Export inventories data to Excel",
     *     description="Export inventories data to an Excel file based on the selected year and month.",
     *     @OA\Response(
     *         response=200,
     *         description="Excel file downloaded successfully",
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad request. Invalid input data.",
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden. User is not authorized to perform this action.",
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error. An unexpected error occurred.",
     *     )
     * )
     *
     *
     *
     */

    ///export inventories table and download as inventories.xlsx file
    public function exportInventories(Request $request)
    {
        try {


            // set validation rules
            $rules = [
                'sku' => 'nullable|string|max:255|exists:inventories,sku,is_deleted,0',
            ];

            // Define custom error messages
            $customMessages = [
                'sku.string' => 'שדה שהוזן אינו בפורמט תקין',
                'sku.max' => 'אורך שדה מק"ט חייב להכיל לכל היותר 255 תווים',
                'sku.exists' => 'שדה מק"ט שנשלח אינו קיים במערכת.',
            ];

            // validate the request with custom error messages
            $validator = Validator::make($request->all(), $rules, $customMessages);


            // Check if validation fails
            if ($validator->fails()) {
                return response()->json(['messages' => $validator->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);
            }



            if ($request->input('sku')) {

                $inventories = Inventory::where('sku', $request->input('sku'))
                    ->where('is_deleted', false)->get()
                    ->map(function ($inventory) {

                        // Format the created_at and updated_at timestamps
                        $inventory->created_at_date = $inventory->created_at->format('d/m/Y');
                        $inventory->updated_at_date = $inventory->updated_at->format('d/m/Y');

                        return $inventory;
                    });

            }else{

                // Fetch all inventories
                $inventories = Inventory::where('is_deleted', false)
                    ->orderBy('created_at', 'desc')
                    ->get()
                    ->map(function ($inventory) {

                        // Format the created_at and updated_at timestamps
                        $inventory->created_at_date = $inventory->created_at->format('d/m/Y');
                        $inventory->updated_at_date = $inventory->updated_at->format('d/m/Y');

                        return $inventory;
                    });
            }


            // Create a new Spreadsheet object
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            $sheet->setRightToLeft(true);

            // Set headers
            $sheet->setCellValue('A1', 'מזהה שורה');
            $sheet->setCellValue('B1', 'כמות');
            $sheet->setCellValue('C1', 'מק"ט');
            $sheet->setCellValue('D1', 'סוג פריט');
            $sheet->setCellValue('E1', 'פירוט מורחב');
            $sheet->setCellValue('F1', 'נוצר בתאריך');
            $sheet->setCellValue('G1', 'עודכן בתאריך');

            $row = 2;
            foreach ($inventories as $inventory) {

                $sheet->setCellValue('A' . $row, $inventory->id ?? 'לא קיים');
                $sheet->setCellValue('B' . $row, $inventory->quantity ?? 'לא קיים');
                $sheet->setCellValue('C' . $row, $inventory->sku ?? 'לא קיים');
                $sheet->setCellValue('D' . $row, $inventory->item_type ?? 'לא קיים');
                $sheet->setCellValue('E' . $row, $inventory->detailed_description ?? 'לא קיים');
                $sheet->setCellValue('F' . $row, $inventory->created_at_date ?? 'לא קיים');
                $sheet->setCellValue('G' . $row, $inventory->created_at_date ?? 'לא קיים');

                $row++;
            }

            // Set & Style the header cells
            $headerStyle = [
                'font' => [
                    'bold' => true,
                    'size' => 12,
                    'name' => 'Arial',
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'color' => ['rgb' => 'D9EAD3'], // Light green fill color
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => '000000'],
                    ],
                ],
            ];

            $sheet->getStyle('A1:O1')->applyFromArray($headerStyle);

            // Set & Style the cells
            $cellStyle = [
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ];

            // apply styling to all cells in the sheet
            $sheet->getStyle('A1:O' . ($row - 1))->applyFromArray($cellStyle);

            // set the size for rest of columns
            foreach (range('A', 'O') as $column) {
                $sheet->getColumnDimension($column)->setAutoSize(true);
            }

            // $filename = 'inventories.xlsx';
            $filename = 'inventories_' . now()->format('Y-m-d_H-i-s') . '.xlsx';

            // Save the file to a temporary location
            $writer = new Xlsx($spreadsheet);
            $writer->save($filename);

            $headers = [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Content-Disposition' => "attachment; filename=\"$filename\"",
            ];

            return response()->download($filename, $filename, $headers)->deleteFileAfterSend(true);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
        }
        return response()->json(['message' => 'התרחש בעיית שרת יש לנסות שוב מאוחר יותר.'], Response::HTTP_INTERNAL_SERVER_ERROR);
    }

    /**
     * @OA\Get(
     *      path="/api/export/inventories-email",
     *      tags={"Export"},
     *      summary="Send inventories email to selected users",
     *      description="Send inventories email to users based on provided user IDs. Optionally, fetch and send a specific inventories by ID.",
     *      @OA\RequestBody(
     *          required=true,
     *          @OA\JsonContent(
     *              type="object",
     *              required={"users"},
     *              @OA\Property(
     *                  property="users",
     *                  type="array",
     *                  @OA\Items(type="integer", example="1", description="User ID")
     *              ),
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Success response",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="message", type="string", example="הנתונים נשלחו בהצלחה.")
     *          )
     *      ),
     *      @OA\Response(
     *          response=400,
     *          description="Bad request response",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="message", type="string", example="הנתונים שנשלחו אינם תקינים.")
     *          )
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="Unprocessable Entity.",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="message", type="string", example="הנתונים שנשלחו אינם בפורמט תקין")
     *          )
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="message", type="string", example="המשתמש אינו מורשה לבצע פעולה זו.")
     *          )
     *      ),
     *      @OA\Response(
     *          response=500,
     *          description="Error response",
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="message", type="string", example="Error sending inventories email")
     *          )
     *      )
     * )
     */

    //? send inventories records by email.
    public function sendInventoriesByEmail(Request $request)
    {
        try {
            // set validation rules
            $rules = [
                'users' => 'required|array',
                'users.*' => 'required|exists:users,id,is_deleted,0',
                'sku' => 'nullable|string|max:255|exists:inventories,sku,is_deleted,0',

            ];

            // Define custom error messages
            $customMessages = [
                'users.required' => 'חובה לשלוח משתמש אחד לפחות.',
                'users.array' => 'שדה משתמש שנשלח אינו תקין.',
                'users.*.required' => 'שדה זה נדרש.',
                'users.*.exists' => 'הערך שהוזן לא חוקי.',

                'sku.string' => 'שדה שהוזן אינו בפורמט תקין',
                'sku.max' => 'אורך שדה מק"ט חייב להכיל לכל היותר 255 תווים',
                'sku.exists' => 'שדה מק"ט שנשלח אינו קיים במערכת.',
            ];

            // validate the request with custom error messages
            $validator = Validator::make($request->all(), $rules, $customMessages);


            // Check if validation fails
            if ($validator->fails()) {
                return response()->json(['messages' => $validator->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            if ($request->input('sku')) {

                $inventories = Inventory::where('sku', $request->input('sku'))
                ->where('is_deleted', false)->get()
                    ->map(function ($inventory) {

                    // Format the created_at and updated_at timestamps
                    $inventory->created_at_date = $inventory->created_at->format('d/m/Y');
                    $inventory->updated_at_date = $inventory->updated_at->format('d/m/Y');

                    return $inventory;
                });

            } else {

                // Fetch all inventories
                $inventories = Inventory::where('is_deleted', false)
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($inventory) {

                    // Format the created_at and updated_at timestamps
                    $inventory->created_at_date = $inventory->created_at->format('d/m/Y');
                    $inventory->updated_at_date = $inventory->updated_at->format('d/m/Y');

                    return $inventory;
                });
            }



            $users = User::whereIn('id', $request->users)->get();

            // Get an array of user emails
            $emails = $users->pluck('email')->toArray();

            // Send email to all users using BCC
            Mail::bcc($emails)->send(new InventoryMail($inventories));

            return response()->json(['message' => 'מייל נשלח בהצלחה'], Response::HTTP_OK);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
        }

        return response()->json(['message' => 'התרחש בעיית שרת יש לנסות שוב מאוחר יותר.'], Response::HTTP_INTERNAL_SERVER_ERROR);
    }




    /**
     * Export users data to Excel file.
     *
     * Exports users data to an Excel file based on the selected year and month.
     *
     * @OA\Post(
     *     path="/api/export/users",
     *     tags={"Export"},
     *     summary="Export users data to Excel",
     *     description="Export users data to an Excel file based on the selected year and month.",
     *     @OA\Response(
     *         response=200,
     *         description="Excel file downloaded successfully",
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad request. Invalid input data.",
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden. User is not authorized to perform this action.",
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error. An unexpected error occurred.",
     *     )
     * )
     *
     *
     *
     */

    public function exportUsers(Request $request)
    {
        try {


            // Fetch users_fetch
            $users = User::where('is_deleted', false)
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($user) {

                    // Format the created_at and updated_at timestamps
                    $user->created_at_date = $user->created_at->format('d/m/Y');
                    $user->updated_at_date = $user->updated_at->format('d/m/Y');

                    return $user;
                });


            // Set a spreadsheet instance
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            // Set direction from right to left
            $sheet->setRightToLeft(true);

            // Set headers
            $sheet->setCellValue('A1', 'מזהה שורה');
            $sheet->setCellValue('B1', 'שם משתמש');
            $sheet->setCellValue('C1', 'מספר אישי');
            $sheet->setCellValue('D1', 'מייל');
            $sheet->setCellValue('E1', 'מספר טלפון');
            $sheet->setCellValue('F1', 'סוג עובד');
            $sheet->setCellValue('G1', 'נוצר בתאריך');
            $sheet->setCellValue('H1', 'עודכן בתאריך');

            $row = 2;
            foreach ($users as $user) {



                $sheet->setCellValue('A' . $row, $user->id ?? 'לא קיים');
                $sheet->setCellValue('B' . $row, $user->name ?? 'לא קיים');
                $sheet->setCellValue('C' . $row, $user->personal_number ?? 'לא קיים');
                $sheet->setCellValue('D' . $row, $user->email ?? 'לא קיים');
                $sheet->setCellValue('E' . $row, $user->phone ?? 'לא קיים');
                $sheet->setCellValue('F' . $row, $user->emp_type_id ? $user->translated_employee_type : 'לא קיים');
                $sheet->setCellValue('G' . $row, $user->created_at_date ?? 'לא קיים');
                $sheet->setCellValue('H' . $row, $user->updated_at_date ?? 'לא קיים');

                $row++;
            }


            // set & style the header cells
            $headerStyle = [
                'font' => [
                    'bold' => true,
                    'size' => 12,
                    'name' => 'Arial',
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'color' => ['rgb' => 'D9EAD3'], // Light green fill color
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => '000000'],
                    ],
                ],
            ];

            //set header style
            $sheet->getStyle('A1:H1')->applyFromArray($headerStyle);

            // Set & Style the cells
            $cellStyle = [
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ];

            // Apply styling to all cells in the sheet
            $sheet->getStyle('A1:H' . ($row - 1))->applyFromArray($cellStyle);

            // Set auto size for columns
            foreach (range('A', 'H') as $column) {
                $sheet->getColumnDimension($column)->setAutoSize(true);
            }


            // create Excel file
            $writer = new Xlsx($spreadsheet);
            // $filename = 'users.xlsx';
            $filename = 'users_' . now()->format('Y-m-d_H-i-s') . '.xlsx';

            $writer->save($filename);

            $headers = [
                "Content-Type" => "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet",
                "Content-Disposition" => "attachment; filename=\"$filename\""
            ];


            return response()->download($filename, $filename, $headers)->deleteFileAfterSend(true);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
        }
        return response()->json(['message' => 'התרחש בעיית שרת, יש לנסות שוב מאוחר יותר.'], Response::HTTP_INTERNAL_SERVER_ERROR);
    }



    /**
     * @OA\Post(
     *      path="/api/export/users-email",
     *      tags={"Export"},
     *      summary="Send email to specified users",
     *      description="Send email to specified users using their user IDs.",
     *      security={{"bearerAuth":{}}},
     *      @OA\RequestBody(
     *          required=true,
     *          description="User IDs to send email to",
     *          @OA\JsonContent(
     *              required={"user_ids"},
     *              @OA\Property(property="user_ids", type="array", @OA\Items(type="integer"), example="[1, 2, 3]", description="Array of user IDs")
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Email sent successfully",
     *          @OA\JsonContent(
     *              @OA\Property(property="status", type="string", example="OK")
     *          )
     *      ),
     *      @OA\Response(
     *          response=400,
     *          description="Bad Request",
     *          @OA\JsonContent(
     *              @OA\Property(property="status", type="string", example="BAD_REQUEST")
     *          )
     *      ),
     *      @OA\Response(
     *          response=409,
     *          description="Conflict",
     *          @OA\JsonContent(
     *              @OA\Property(property="status", type="string", example="CONFLICT")
     *          )
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="Unprocessable Entity",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Invalid data was sent")
     *          )
     *      ),
     *      @OA\Response(
     *          response=500,
     *          description="Internal Server Error",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Error sending email")
     *          )
     *      )
     * )
     */


    public function sendUsersByEmail(Request $request)
    {
        try {


            // set validation rules
            $rules = [
                'users' => 'required|array',
                'users.*' => 'required|exists:users,id,is_deleted,0',
            ];

            // Define custom error messages
            $customMessages = [
                'users.required' => 'חובה לשלוח משתמש אחד לפחות.',
                'users.array' => 'שדה משתמש שנשלח אינו תקין.',
                'users.*.required' => 'שדה זה נדרש.',
                'users.*.exists' => 'הערך שהוזן לא חוקי.',
            ];

            // validate the request with custom error messages
            $validator = Validator::make($request->all(), $rules, $customMessages);


            // Check if validation fails
            if ($validator->fails()) {
                return response()->json(['messages' => $validator->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);
            }



            // Fetch users_fetch
            $users_fetch = User::where('is_deleted', false)
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($user) {

                // Format the created_at and updated_at timestamps
                $user->created_at_date = $user->created_at->format('d/m/Y');
                $user->updated_at_date = $user->updated_at->format('d/m/Y');

                return $user;
            });


            $users = User::whereIn('id', $request->users)->get();

            // Get an array of user emails
            $emails = $users->pluck('email')->toArray();

            // Send email to all users using BCC
            Mail::bcc($emails)->send(new UserMail($users_fetch));

            return response()->json(['message' => 'מייל נשלח בהצלחה'], Response::HTTP_OK);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
        }

        return response()->json(['message' => 'התרחש בעיית שרת יש לנסות שוב מאוחר יותר.'], Response::HTTP_INTERNAL_SERVER_ERROR);
    }


    /**
     * Export distributions data to Excel file.
     *
     * Exports distributions data to an Excel file based on the selected year and month.
     *
     * @OA\Post(
     *     path="/api/export/distributions",
     *     tags={"Export"},
     *     summary="Export distributions data to Excel",
     *     description="Export distributions data to an Excel file based on the selected year and month.",
     *     @OA\Response(
     *         response=200,
     *         description="Excel file downloaded successfully",
     *     ),
     *     @OA\Response(
     *         response=400,
     *         description="Bad request. Invalid input data.",
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden. User is not authorized to perform this action.",
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal Server Error. An unexpected error occurred.",
     *     )
     * )
     *
     *
     *
     */

    public function exportDistributions(Request $request)
    {
        try {


            // // set validation rules
            // $rules = [
            //     'sku' => 'nullable|string|max:255|exists:inventories,sku,is_deleted,0',
            // ];

            // // Define custom error messages
            // $customMessages = [
            //     'sku.string' => 'שדה שהוזן אינו בפורמט תקין',
            //     'sku.max' => 'אורך שדה מק"ט חייב להכיל לכל היותר 255 תווים',
            //     'sku.exists' => 'שדה מק"ט שנשלח אינו קיים במערכת.',
            // ];

            // // validate the request with custom error messages
            // $validator = Validator::make($request->all(), $rules, $customMessages);


            // // Check if validation fails
            // if ($validator->fails()) {
            //     return response()->json(['messages' => $validator->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);
            // }



            // if ($request->input('sku')) {
            //     $inventories = Inventory::where('sku', $request->input('sku'))
            //     ->where('is_deleted', false)->get();
            // } else {

            //     // Fetch all inventories
            //     $inventories = Inventory::where('is_deleted', false)->get();
            // }


            $distributions = Distribution::with(['inventory', 'department', 'createdByUser'])
                ->where('is_deleted', 0)
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($distribution) {

                    // Format the created_at and updated_at timestamps
                    $distribution->created_at_date = $distribution->created_at->format('d/m/Y');
                    $distribution->updated_at_date = $distribution->updated_at->format('d/m/Y');

                    return $distribution;
                });

            // Create a new Spreadsheet object
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            $sheet->setRightToLeft(true);

            // Set headers
            $sheet->setCellValue('A1', 'מזהה שורה');
            $sheet->setCellValue('B1', 'תאריך ניפוק');
            $sheet->setCellValue('C1', 'שם מחלקה');
            $sheet->setCellValue('D1', 'מספר אישי');
            $sheet->setCellValue('E1', 'שם מלא');
            $sheet->setCellValue('F1', 'סוג עובד');
            $sheet->setCellValue('G1', 'טלפון');
            $sheet->setCellValue('H1', 'מייל');
            $sheet->setCellValue('I1', 'כמות');
            $sheet->setCellValue('J1', 'מק"ט');
            $sheet->setCellValue('K1', 'סוג פריט');
            $sheet->setCellValue('L1', 'פירוט מורחב');
            $sheet->setCellValue('M1', 'הערות');
            $sheet->setCellValue('N1', 'סטטוס');
            $sheet->setCellValue('O1', 'תאריך שינוי אחרון');

            $row = 2;
            foreach ($distributions as $distribution) {


                $sheet->setCellValue('A' . $row, $distribution->id ?? 'לא קיים');
                $sheet->setCellValue('B' . $row, $distribution->created_at_date ?? 'לא קיים');
                $sheet->setCellValue('C' . $row, $distribution->department_id ? $distribution->department->name : 'לא קיים');
                $sheet->setCellValue('D' . $row, $distribution->created_by ? $distribution->createdByUser->personal_number : 'לא קיים');
                $sheet->setCellValue('E' . $row, $distribution->created_by ? $distribution->createdByUser->name : 'לא קיים');
                $sheet->setCellValue('F' . $row, $distribution->created_by ? $distribution->createdByUser->translated_employee_type : 'לא קיים');
                $sheet->setCellValue('G' . $row, $distribution->created_by ? $distribution->createdByUser->phone : 'לא קיים');
                $sheet->setCellValue('H' . $row, $distribution->created_by ? $distribution->createdByUser->email : 'לא קיים');
                $sheet->setCellValue('I' . $row, $distribution->quantity ?? 'לא קיים');
                $sheet->setCellValue('J' . $row, $distribution->inventory_id ? $distribution->inventory->sku : 'לא קיים');
                $sheet->setCellValue('K' . $row, $distribution->inventory_id ? $distribution->inventory->item_type : 'לא קיים');
                $sheet->setCellValue('L' . $row, $distribution->inventory_id ? $distribution->inventory->detailed_description : 'לא קיים');
                $sheet->setCellValue('M' . $row, $distribution->comment ?? 'לא קיים');
                $sheet->setCellValue('N' . $row, $distribution->getStatusTranslation() ?? 'לא קיים');
                $sheet->setCellValue('O' . $row, $distribution->updated_at_date ?? 'לא קיים');

                $row++;
            }

            // Set & Style the header cells
            $headerStyle = [
                'font' => [
                    'bold' => true,
                    'size' => 12,
                    'name' => 'Arial',
                ],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'color' => ['rgb' => 'D9EAD3'], // Light green fill color
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
                'borders' => [
                    'allBorders' => [
                        'borderStyle' => Border::BORDER_THIN,
                        'color' => ['rgb' => '000000'],
                    ],
                ],
            ];

            $sheet->getStyle('A1:O1')->applyFromArray($headerStyle);

            // Set & Style the cells
            $cellStyle = [
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ];

            // apply styling to all cells in the sheet
            $sheet->getStyle('A1:O' . ($row - 1))->applyFromArray($cellStyle);

            // set the size for rest of columns
            foreach (range('A', 'O') as $column) {
                $sheet->getColumnDimension($column)->setAutoSize(true);
            }

            // $filename = 'inventories.xlsx';
            $filename = 'distributions_' . now()->format('Y-m-d_H-i-s') . '.xlsx';

            // Save the file to a temporary location
            $writer = new Xlsx($spreadsheet);
            $writer->save($filename);

            $headers = [
                'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                'Content-Disposition' => "attachment; filename=\"$filename\"",
            ];

            return response()->download($filename, $filename, $headers)->deleteFileAfterSend(true);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
        }
        return response()->json(['message' => 'התרחש בעיית שרת יש לנסות שוב מאוחר יותר.'], Response::HTTP_INTERNAL_SERVER_ERROR);
    }

    /**
     * @OA\Post(
     *      path="/api/export/distributions-email",
     *      tags={"Export"},
     *      summary="Send email to specified distributions",
     *      description="Send email to specified distributions using their user IDs.",
     *      security={{"bearerAuth":{}}},
     *      @OA\RequestBody(
     *          required=true,
     *          description="User IDs to send email to",
     *          @OA\JsonContent(
     *              required={"user_ids"},
     *              @OA\Property(property="user_ids", type="array", @OA\Items(type="integer"), example="[1, 2, 3]", description="Array of user IDs")
     *          )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Email sent successfully",
     *          @OA\JsonContent(
     *              @OA\Property(property="status", type="string", example="OK")
     *          )
     *      ),
     *      @OA\Response(
     *          response=400,
     *          description="Bad Request",
     *          @OA\JsonContent(
     *              @OA\Property(property="status", type="string", example="BAD_REQUEST")
     *          )
     *      ),
     *      @OA\Response(
     *          response=409,
     *          description="Conflict",
     *          @OA\JsonContent(
     *              @OA\Property(property="status", type="string", example="CONFLICT")
     *          )
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="Unprocessable Entity",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Invalid data was sent")
     *          )
     *      ),
     *      @OA\Response(
     *          response=500,
     *          description="Internal Server Error",
     *          @OA\JsonContent(
     *              @OA\Property(property="message", type="string", example="Error sending email")
     *          )
     *      )
     * )
     */


    public function sendDistributionsByEmail(Request $request)
    {
        try {
            // set validation rules
            $rules = [
                'users' => 'required|array',
                'users.*' => 'required|exists:users,id,is_deleted,0',
                // 'sku' => 'nullable|string|max:255|exists:inventories,sku,is_deleted,0',

            ];

            // Define custom error messages
            $customMessages = [
                'users.required' => 'חובה לשלוח משתמש אחד לפחות.',
                'users.array' => 'שדה משתמש שנשלח אינו תקין.',
                'users.*.required' => 'שדה זה נדרש.',
                'users.*.exists' => 'הערך שהוזן לא חוקי.',

                // 'sku.string' => 'שדה שהוזן אינו בפורמט תקין',
                // 'sku.max' => 'אורך שדה מק"ט חייב להכיל לכל היותר 255 תווים',
                // 'sku.exists' => 'שדה מק"ט שנשלח אינו קיים במערכת.',
            ];

            // validate the request with custom error messages
            $validator = Validator::make($request->all(), $rules, $customMessages);


            // Check if validation fails
            if ($validator->fails()) {
                return response()->json(['messages' => $validator->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);
            }

            if ($request->input('sku')) {
                $distributions = Distribution::where('sku', $request->input('sku'))
                    ->where('is_deleted', false)->get();
            } else {

                // Fetch all distributions
                // $distributions = Distribution::where('is_deleted', false)->get();


                $distributions = Distribution::with(['inventory', 'department', 'createdByUser'])
                    ->where('is_deleted', 0)
                    ->orderBy('created_at', 'desc')
                    ->get()
                    ->map(function ($distribution) {

                        // Format the created_at and updated_at timestamps
                        $distribution->created_at_date = $distribution->created_at->format('d/m/Y');
                        $distribution->updated_at_date = $distribution->updated_at->format('d/m/Y');

                        return $distribution;
                    });
            }



            $users = User::whereIn('id', $request->users)->get();

            // Get an array of user emails
            $emails = $users->pluck('email')->toArray();

            // Send email to all users using BCC
            Mail::bcc($emails)->send(new DistributionMail($distributions));

            return response()->json(['message' => 'מייל נשלח בהצלחה'], Response::HTTP_OK);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
        }

        return response()->json(['message' => 'התרחש בעיית שרת יש לנסות שוב מאוחר יותר.'], Response::HTTP_INTERNAL_SERVER_ERROR);
    }




}