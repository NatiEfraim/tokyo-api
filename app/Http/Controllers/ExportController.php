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
use Illuminate\Validation\Rule;

use Illuminate\Support\Facades\Validator;

class ExportController extends Controller
{
    //

    /**
     * Export inventories data to Excel file.
     *
     * Exports inventories data to an Excel file based on the selected year and month.
     *
     * @OA\Get(
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

                $inventories = Inventory::with(['itemType'])
                    ->where('sku', $request->input('sku'))
                    ->where('is_deleted', false)
                    ->orderBy('created_at', 'desc')
                    ->get()
                    ->map(function ($inventory) {

                        // Format the created_at and updated_at timestamps
                        $inventory->created_at_date = $inventory->created_at->format('d/m/Y');
                        $inventory->updated_at_date = $inventory->updated_at->format('d/m/Y');
                        $inventory->available = $inventory->quantity - $inventory->reserved;

                        return $inventory;
                    });

            }else{

                // Fetch all inventories
                $inventories = Inventory::with(['itemType'])
                    ->where('is_deleted', false)
                    ->orderBy('created_at', 'desc')
                    ->get()
                    ->map(function ($inventory) {

                        // Format the created_at and updated_at timestamps
                        $inventory->created_at_date = $inventory->created_at->format('d/m/Y');
                        $inventory->updated_at_date = $inventory->updated_at->format('d/m/Y');
                        $inventory->available = $inventory->quantity - $inventory->reserved;


                        return $inventory;
                    });
            }


            // Create a new Spreadsheet object
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            $sheet->setRightToLeft(true);

            // Set headers
            $sheet->setCellValue('A1', 'מזהה שורה');
            $sheet->setCellValue('B1', 'מלאי זמין');
            $sheet->setCellValue('C1', 'מק"ט');
            $sheet->setCellValue('D1', 'סוג פריט');
            $sheet->setCellValue('E1', 'פירוט מורחב');
            $sheet->setCellValue('F1', 'שמורים');
            $sheet->setCellValue('G1', 'נוצר בתאריך');
            $sheet->setCellValue('H1', 'עודכן בתאריך');

            $row = 2;
            foreach ($inventories as $inventory) {

                $sheet->setCellValue('A' . $row, $inventory->id ?? 'לא קיים');
                $sheet->setCellValue('B' . $row, $inventory->available ?? 'לא קיים');
                $sheet->setCellValue('C' . $row, $inventory->sku ?? 'לא קיים');
                 $sheet->setCellValue('D' . $row, $inventory->itemType->type ?? 'לא קיים');
                $sheet->setCellValue('E' . $row, $inventory->detailed_description ?? 'לא קיים');
                $sheet->setCellValue('F' . $row, $inventory->reserved ?? 'לא קיים');
                $sheet->setCellValue('G' . $row, $inventory->created_at_date ?? 'לא קיים');
                $sheet->setCellValue('H' . $row, $inventory->created_at_date ?? 'לא קיים');



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

            $sheet->getStyle('A1:H1')->applyFromArray($headerStyle);

            // Set & Style the cells
            $cellStyle = [
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ];

            // apply styling to all cells in the sheet
            $sheet->getStyle('A1:H' . ($row - 1))->applyFromArray($cellStyle);

            // set the size for rest of columns
            foreach (range('A', 'H') as $column) {
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
                ->where('is_deleted', false)
                ->orderBy('created_at', 'desc')
                ->get()
                    ->map(function ($inventory) {

                    // Format the created_at and updated_at timestamps
                    $inventory->created_at_date = $inventory->created_at->format('d/m/Y');
                    $inventory->updated_at_date = $inventory->updated_at->format('d/m/Y');
                    $inventory->available = $inventory->quantity - $inventory->reserved;


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
                    $inventory->available = $inventory->quantity - $inventory->reserved;


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
     * @OA\Get(
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
     * @OA\Get(
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
     * @OA\Get(
     *     path="/api/export/distributions",
     *     summary="Export distributions",
     *     description="Export distributions data based on provided filters",
     *     tags={"Export"},
     *     @OA\Parameter(
     *         name="sku",
     *         in="query",
     *         description="SKU of the inventory",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="status",
     *         in="query",
     *         description="Status of the distribution (0: pending, 1: approved, 2: canceled)",
     *         @OA\Schema(type="integer", format="int32", minimum=0, maximum=2)
     *     ),
     *     @OA\Parameter(
     *         name="name",
     *         in="query",
     *         description="Name of the department",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="personal_number",
     *         in="query",
     *         description="Personal number of the creator",
     *         @OA\Schema(type="string", minLength=1, maxLength=7)
     *     ),
     *     @OA\Parameter(
     *         name="created_at",
     *         in="query",
     *         description="Creation date of the distribution (YYYY-MM-DD)",
     *         @OA\Schema(type="string", format="date")
     *     ),
     *     @OA\Parameter(
     *         name="updated_at",
     *         in="query",
     *         description="Last updated date of the distribution (YYYY-MM-DD)",
     *         @OA\Schema(type="string", format="date")
     *     ),
     *      @OA\Response(
     *          response=200,
     *          description="Email sent successfully",
     *          @OA\JsonContent(
     *              @OA\Property(property="status", type="string", example="OK")
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

    public function exportDistributions(Request $request)
    {
        try {

            // set validation rules
            $rules = [


                'sku' => 'nullable|string|max:255|exists:inventories,sku,is_deleted,0',
                'inventory_id' => 'nullable|string|max:255|exists:inventories,id,is_deleted,0',

                'status' => 'nullable|integer|between:0,2',

                // 'name' => 'nullable|string|exists:departments,name,is_deleted,0',
                'department_id' => 'nullable|string|exists:departments,id,is_deleted,0',

                'personal_number' => 'nullable|min:1|max:7',
                'user_id' => 'nullable|string|exists:users,id,is_deleted,0',


                'created_at' => [
                    'nullable',
                    'date',
                ],

                'updated_at' => [
                    'nullable',
                    'date',
                ],


            ];

            // Define custom error messages
            $customMessages = [



                'sku.string' => 'שדה שהוזן אינו בפורמט תקין',
                'sku.max' => 'אורך שדה מק"ט חייב להכיל לכל היותר 255 תווים',
                'sku.exists' => 'שדה מק"ט שנשלח אינו קיים במערכת.',

                'inventory_id.string' => 'שדה שהוזן אינו בפורמט תקין',
                'inventory_id.max' => 'אורך שדה מק"ט חייב להכיל לכל היותר 255 תווים',
                'inventory_id.exists' => 'שדה מק"ט שנשלח אינו קיים במערכת.',


                'name.string' => 'שדה ערך שם מחלקה אינו תקין.',
                
                'department_id.exists' => 'מחלקה אינה קיימת במערכת.',

                'status.between' => 'שדה הסטטוס אינו תקין.',


                'personal_number.min' => 'מספר אישי אינו תקין.',
                'personal_number.max' => 'מספר אישי אינו תקין.',
                'user_id.exists' => 'משתמש אינו קיים במערכת.',



                'created_at.date' => 'שדה תאריך התחלה אינו תקין.',
                'created_at.exists' => 'שדה תאריך אינו קיים במערכת.',
                'updated_at.date' => 'שדה תאריך סיום אינו תקין.',
                'updated_at.exists' => 'שדה תאריך סיום אינו קיים במערכת.',


            ];

            // validate the request with custom error messages
            $validator = Validator::make($request->all(), $rules, $customMessages);


            // Check if validation fails
            if ($validator->fails()) {
                return response()->json(['messages' => $validator->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);
            }


            if (
                $request->has('user_id')
                || $request->has('status')
                || $request->has('inventory_id')
                || $request->has('department_id')
                || $request->has('created_at')
                || $request->has('updated_at')
            ) {
                //? one or more of th search based on value filter send

                $distributions = $this->fetchDistributions($request);

                if ($distributions) {

                    $distributions->map(function ($distribution) {
                        $distribution->created_at_date = $distribution->created_at->format('d/m/Y');
                        $distribution->updated_at_date = $distribution->updated_at->format('d/m/Y');

                        return $distribution;
                    });
                }

                // Loop through each record and add inventory_items object
                $distributions->transform(function ($distribution) {
                    $inventoryItems = json_decode($distribution->inventory_items, true);
                    // If inventory_items is not null, process it
                    if ($inventoryItems) {
                        $inventoryItems = array_map(function ($item) {
                            return [
                                'sku' => $item['sku'],
                                'quantity' => $item['quantity'],
                            ];
                        }, $inventoryItems);
                    }
                    $distribution->inventory_items = $inventoryItems;
                    return $distribution;
                });


                

            }
            
            else {

                
                // //? fetch all distributions records.

                // Fetch all distributions records.
                $distributions = Distribution::with(['inventory', 'itemType', 'department', 'createdForUser'])
                    ->where('is_deleted', 0)
                    ->orderBy('created_at', 'desc')
                    ->get()
                    ->map(function ($distribution) {
                        // Format the created_at and updated_at timestamps
                        $distribution->created_at_date = $distribution->created_at->format('d/m/Y');
                        $distribution->updated_at_date = $distribution->updated_at->format('d/m/Y');
                        return $distribution;
                    });

                // Loop through each record and add inventory_items object
                $distributions->transform(function ($distribution) {
                    $inventoryItems = json_decode($distribution->inventory_items, true);
                    // If inventory_items is not null, process it
                    if ($inventoryItems) {
                        $inventoryItems = array_map(function ($item) {
                            return [
                                'sku' => $item['sku'],
                                'quantity' => $item['quantity'],
                            ];
                        }, $inventoryItems);
                    }
                    $distribution->inventory_items = $inventoryItems;
                    return $distribution;
                });
                
            }



            // Create a new Spreadsheet object
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();

            $sheet->setRightToLeft(true);

            // Set the header row for the Excel sheet
            $sheet->setCellValue('A1', 'מזהה שורה');
            $sheet->setCellValue('B1', 'תאריך ניפוק');
            $sheet->setCellValue('C1', 'מספר הזמנה');
            $sheet->setCellValue('D1', 'שם מחלקה');
            $sheet->setCellValue('E1', 'מספר אישי');
            $sheet->setCellValue('F1', 'שם מלא');
            $sheet->setCellValue('G1', 'סוג עובד');
            $sheet->setCellValue('H1', 'טלפון');
            $sheet->setCellValue('I1', 'מייל');
            $sheet->setCellValue('J1', 'כמות פריט');
            $sheet->setCellValue('K1', 'כמות סה"כ');
            $sheet->setCellValue('L1', 'סוג פריט');
            $sheet->setCellValue('M1', 'הערות על הפריט');
            $sheet->setCellValue('N1', 'הערות ראש מדור');
            $sheet->setCellValue('O1', 'הערות מנהל');
            $sheet->setCellValue('P1', 'הערות אפסנאי');
            $sheet->setCellValue('Q1', 'סטטוס');
            $sheet->setCellValue('R1', 'תאריך שינוי אחרון');
            $sheet->setCellValue('S1', 'פרטי מלאי');

                        $row = 2;

            foreach ($distributions as $distribution) {
                // Add the main distribution data
                $sheet->setCellValue('A' . $row, $distribution->id ?? 'לא קיים');
                $sheet->setCellValue('B' . $row, $distribution->created_at_date ?? 'לא קיים');
                $sheet->setCellValue('C' . $row, $distribution->order_number ?? 'לא קיים');
                $sheet->setCellValue('D' . $row, $distribution->created_for ? $distribution->createdForUser->department->name  : 'לא קיים');
                $sheet->setCellValue('E' . $row, $distribution->created_for ? $distribution->createdForUser->personal_number : 'לא קיים');
                $sheet->setCellValue('F' . $row, $distribution->created_for ? $distribution->createdForUser->name : 'לא קיים');
                $sheet->setCellValue('G' . $row, $distribution->created_for ? $distribution->createdForUser->translated_employee_type : 'לא קיים');
                $sheet->setCellValue('H' . $row, $distribution->created_for ? $distribution->createdForUser->phone : 'לא קיים');
                $sheet->setCellValue('I' . $row, $distribution->created_for ? $distribution->createdForUser->email : 'לא קיים');
                $sheet->setCellValue('J' . $row, $distribution->quantity_per_item ?? 'לא קיים');
                $sheet->setCellValue('K' . $row, $distribution->total_quantity ?? 'לא קיים');
                $sheet->setCellValue('L' . $row, $distribution->type_id ? $distribution->itemType->type : 'לא קיים');
                $sheet->setCellValue('M' . $row, $distribution->type_comment ?? 'לא קיים');
                $sheet->setCellValue('N' . $row, $distribution->user_comment ?? 'לא קיים');
                $sheet->setCellValue('O' . $row, $distribution->admin_comment ?? 'לא קיים');
                $sheet->setCellValue('P' . $row, $distribution->quartermaster_comment ?? 'לא קיים');
                $sheet->setCellValue('Q' . $row, $distribution->getStatusTranslation() ?? 'לא קיים');
                $sheet->setCellValue('R' . $row, $distribution->updated_at_date ?? 'לא קיים');

                // Add the inventory items if available
                if (!empty($distribution->inventory_items)) {
                    foreach ($distribution->inventory_items as $item) {
                        $sheet->setCellValue('S' . $row, 'SKU: ' . $item['sku'] . ', Quantity: ' . $item['quantity']);
                        // Move to the next row for the next inventory item
                        $row++;
                    }
                } else {
                    // Move to the next row if there are no inventory items
                    $row++;
                }
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

            $sheet->getStyle('A1:S1')->applyFromArray($headerStyle);

            // Set & Style the cells
            $cellStyle = [
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                    'vertical' => Alignment::VERTICAL_CENTER,
                ],
            ];

            // apply styling to all cells in the sheet
            $sheet->getStyle('A1:S' . ($row - 1))->applyFromArray($cellStyle);

            // set the size for rest of columns
            foreach (range('A', 'S') as $column) {
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
     *     path="/api/send-distributions-by-email",
     *     tags={"Distributions"},
     *     summary="Send distributions by email",
     *     description="Send distribution records by email to specified users.",
     *     operationId="sendDistributionsByEmail",
     *     security={{"bearerAuth":{}}},
     *     @OA\RequestBody(
     *         required=true,
     *         description="Request body",
     *         @OA\JsonContent(
     *             required={"users"},
     *             type="object",
     *             @OA\Property(
     *                 property="users",
     *                 description="Array of user IDs to send email to",
     *                 type="array",
     *                 @OA\Items(
     *                     type="integer",
     *                     example=1
     *                 )
     *             ),
     *             @OA\Property(
     *                 property="sku",
     *                 description="SKU of the inventory",
     *                 type="string",
     *                 maxLength=255,
     *                 example="ABC123",
     *             ),
     *             @OA\Property(
     *                 property="status",
     *                 description="Status of the distribution",
     *                 type="integer",
     *                 enum={0, 1, 2},
     *                 example=1
     *             ),
     *             @OA\Property(
     *                 property="name",
     *                 description="Name of the department",
     *                 type="string",
     *                 example="Sales"
     *             ),
     *             @OA\Property(
     *                 property="personal_number",
     *                 description="Personal number of the user",
     *                 type="string",
     *                 minLength=1,
     *                 maxLength=7,
     *                 example="1234567"
     *             ),
     *             @OA\Property(
     *                 property="created_at",
     *                 description="Filter by creation date (YYYY-MM-DD)",
     *                 type="string",
     *                 format="date",
     *                 example="2024-04-15"
     *             ),
     *             @OA\Property(
     *                 property="updated_at",
     *                 description="Filter by last updated date (YYYY-MM-DD)",
     *                 type="string",
     *                 format="date",
     *                 example="2024-04-15"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Success response",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="Email sent successfully"
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="messages",
     *                 type="object",
     *                 example={
     *                     "sku": {"The SKU field is not in the correct format."},
     *                     "personal_number": {"The personal number field must be between 1 and 7 characters."},
     *
     *                 }
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Internal server error",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="message",
     *                 type="string",
     *                 example="An internal server error occurred. Please try again later."
     *             )
     *         )
     *     )
     * )
     */

    public function sendDistributionsByEmail(Request $request)
    {
        try {
            // set validation rules
            $rules = [

                'users' => 'required|array',
                'users.*' => 'required|exists:users,id,is_deleted,0',

                
                'status' => 'nullable|integer|between:0,2',
                
                'department_id' => 'nullable|string|exists:departments,id,is_deleted,0',
                
                
                'personal_number' => 'nullable|min:1|max:7',
                
                
                'created_at' => [
                    'nullable',
                    'date',
                ],
                
                'updated_at' => [
                    'nullable',
                    'date',
                ],
                
                
                
                // 'sku' => 'nullable|string|max:255|exists:inventories,sku,is_deleted,0',
                // 'name' => 'nullable|string|exists:departments,name,is_deleted,0',
            ];

            // Define custom error messages
            $customMessages = [


                'users.required' => 'חובה לשלוח משתמש אחד לפחות.',
                'users.array' => 'שדה משתמש שנשלח אינו תקין.',
                'users.*.required' => 'שדה זה נדרש.',
                'users.*.exists' => 'הערך שהוזן לא חוקי.',


                'department_id.exists' => 'מחלקה אינה קיימת במערכת.',

                
                
                'name.string' => 'שדה ערך שם מחלקה אינו תקין.',
                
                'status.between' => 'שדה הסטטוס אינו תקין.',
                
                
                'personal_number.min' => 'מספר אישי אינו תקין.',
                'personal_number.max' => 'מספר אישי אינו תקין.',
                
                
                
                'created_at.date' => 'שדה תאריך התחלה אינו תקין.',
                'created_at.exists' => 'שדה תאריך אינו קיים במערכת.',
                'updated_at.date' => 'שדה תאריך סיום אינו תקין.',
                'updated_at.exists' => 'שדה תאריך סיום אינו קיים במערכת.',
                
                // 'sku.max' => 'אורך שדה מק"ט חייב להכיל לכל היותר 255 תווים',
                // 'sku.string' => 'שדה שהוזן אינו בפורמט תקין',
                // 'sku.exists' => 'שדה מק"ט שנשלח אינו קיים במערכת.',
            ];

            // validate the request with custom error messages
            $validator = Validator::make($request->all(), $rules, $customMessages);


            // Check if validation fails
            if ($validator->fails()) {
                return response()->json(['messages' => $validator->errors()], Response::HTTP_UNPROCESSABLE_ENTITY);
            }


            if (
                $request->has('status')
                || $request->has('department_id')
                || $request->has('personal_number')
                || $request->has('created_at')
                || $request->has('updated_at')
            ) {
                //? one or more of th search based on value filter send
                $distributions = $this->fetchDistributions($request);
                if ($distributions) {

                    $distributions->map(function ($distribution) {
                        $distribution->created_at_date = $distribution->created_at->format('d/m/Y');
                        $distribution->updated_at_date = $distribution->updated_at->format('d/m/Y');

                        return $distribution;
                    });
                }

                // Loop through each record and add inventory_items object
                $distributions->transform(function ($distribution) {
                    $inventoryItems = json_decode($distribution->inventory_items, true);
                    // If inventory_items is not null, process it
                    if ($inventoryItems) {
                        $inventoryItems = array_map(function ($item) {
                            return [
                                'sku' => $item['sku'],
                                'quantity' => $item['quantity'],
                            ];
                        }, $inventoryItems);
                    }
                    $distribution->inventory_items = $inventoryItems;
                    return $distribution;
                });

                
            } else {
                //? fetch all distributions records.
                $distributions = Distribution::with(['inventory', 'department', 'createdForUser'])
                ->where('is_deleted', 0)
                ->orderBy('created_at', 'desc')
                ->get()
                ->map(function ($distribution) {

                    // Format the created_at and updated_at timestamps
                    $distribution->created_at_date = $distribution->created_at->format('d/m/Y');
                    $distribution->updated_at_date = $distribution->updated_at->format('d/m/Y');

                    return $distribution;
                });


                // Loop through each record and add inventory_items object
                $distributions->transform(function ($distribution) {
                    $inventoryItems = json_decode($distribution->inventory_items, true);
                    // If inventory_items is not null, process it
                    if ($inventoryItems) {
                        $inventoryItems = array_map(function ($item) {
                            return [
                                'sku' => $item['sku'],
                                'quantity' => $item['quantity'],
                            ];
                        }, $inventoryItems);
                    }
                    $distribution->inventory_items = $inventoryItems;
                    return $distribution;
                });

                
            }

            dd($distributions->toArray());

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



    //? search based on request->input('query').
    private function fetchDistributions(Request $request)
    {
        try {


            $query = Distribution::query();





            //? search by the associated id

            //? fetch by status
            if ($request->has('status')) {
                $query->where('status', $request->status);
            }


            //? fetch by department
            if ($request->has('department_id')) {

                
                $query->whereHas('createdForUser', function ($q) use ($request) {
                    $q->where('department_id', $request->input('department_id'));
                });
            }


            // Search by order_number
            if ($request->has('order_number')) {
                $query->where('order_number', $request->input('order_number'));
            }

            if ($request->has('user_id')) {
                // $pnInput = $request->personal_number;
                $query->whereHas('createdForUser', function ($q) use ($request) {
                    // $q->whereRaw('SUBSTRING(personal_number, 2) LIKE ?', ['%' . $request->input('personal_number') . '%']);
                    $q->where('id', $request->input('user_id'));
                });
            }


            if ($request->has('created_at')) {
                $query->whereDate('created_at', $request->created_at);
            }

            if ($request->has('updated_at')) {
                $query->whereDate('updated_at', $request->updated_at);
            }

            // Ensure is_deleted is 0
            $query->where('is_deleted', 0);

            return $query->with(['inventory', 'department', 'itemType','createdForUser'])
            ->orderBy('created_at', 'desc')
            ->get();
        } catch (\Exception $e) {
            Log::error($e->getMessage());
        }
        return response()->json(['message' => 'התרחש בעיית שרת יש לנסות שוב מאוחר יותר.'], Response::HTTP_INTERNAL_SERVER_ERROR);

    }


}